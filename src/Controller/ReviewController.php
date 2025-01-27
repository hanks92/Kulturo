<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Form\FlashcardType;
use App\Repository\RevisionRepository;
use App\Service\FSRSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FSRSService $fsrsService;

    public function __construct(EntityManagerInterface $entityManager, FSRSService $fsrsService)
    {
        $this->entityManager = $entityManager;
        $this->fsrsService = $fsrsService;
    }

    #[Route('/deck/{id}/flashcard/create', name: 'flashcard_create')]
    public function createFlashcard(Deck $deck, Request $request): Response
    {
        // Vérifie que le deck appartient à l'utilisateur connecté
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Vous n\'avez pas accès à ce deck.');
        }

        // Crée une nouvelle flashcard
        $flashcard = new Flashcard();
        $flashcard->setDeck($deck);

        // Formulaire pour créer une flashcard
        $form = $this->createForm(FlashcardType::class, $flashcard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder la flashcard dans la base de données
            $this->entityManager->persist($flashcard);
            $this->entityManager->flush();

            // Appel à FSRS pour initialiser les données de la révision
            $revisionData = $this->fsrsService->initializeCard($flashcard->getId());

            if (!$revisionData) {
                $this->addFlash('error', 'Échec de l\'initialisation de la révision via FSRS.');
                return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
            }

            // Crée une nouvelle révision avec les données retournées
            $revision = new Revision();
            $revision->setFlashcard($flashcard);
            $revision->setStability($revisionData['stability'] ?? 1.0);
            $revision->setRetrievability($revisionData['retrievability'] ?? 0.9);
            $revision->setDifficulty($revisionData['difficulty'] ?? 5.0);
            $revision->setState($revisionData['state'] ?? 1); // État initial (Learning)
            $revision->setStep($revisionData['step'] ?? 0);
            $revision->setDueDate(new \DateTime($revisionData['due'] ?? 'now'));

            // Sauvegarde la révision dans la base de données
            $this->entityManager->persist($revision);
            $this->entityManager->flush();

            $this->addFlash('success', 'Flashcard et révision créées avec succès !');

            return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
        }

        return $this->render('flashcard/create.html.twig', [
            'form' => $form->createView(),
            'deck' => $deck,
        ]);
    }

    #[Route('/review/start/{deckId<\d+>}', name: 'app_review_start')]
    public function start(int $deckId, RevisionRepository $revisionRepository): Response
    {
        // Récupérer le Deck
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        // Vérifie que le Deck existe et appartient à l'utilisateur
        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        // Récupérer la première carte à réviser pour ce deck
        $revision = $revisionRepository->findNextFlashcardForTodayByDeck($deck);

        // Si aucune carte à réviser, afficher un message de fin
        if (!$revision) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        // Rediriger vers la session avec la première carte
        return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
    }

    #[Route('/review/session/{id<\d+>}', name: 'app_review_session')]
    public function session(Revision $revision): Response
    {
        // Vérifier que la révision appartient bien à un deck de l'utilisateur
        $deck = $revision->getFlashcard()->getDeck();
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }

        // Afficher la révision actuelle
        return $this->render('review/index.html.twig', [
            'revision' => $revision,
            'flashcard' => $revision->getFlashcard(),
        ]);
    }
}
