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
use DateTime;

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
            $this->entityManager->flush(); // Nécessaire pour générer un ID pour la flashcard

            // Appel à FSRS pour initialiser les données de la révision
            $revisionData = $this->fsrsService->initializeCard($flashcard->getId());

            if (!$revisionData) {
                $this->addFlash('error', 'Échec de l\'initialisation de la révision via FSRS.');
                return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
            }

            // Crée une nouvelle révision avec les données retournées
            $revision = new Revision();
            $revision->setFlashcard($flashcard); // Associe la révision à la flashcard
            $revision->setStability($revisionData['stability']);
            $revision->setRetrievability($revisionData['retrievability']);
            $revision->setDifficulty($revisionData['difficulty']);
            $revision->setState($revisionData['state']);
            $revision->setStep($revisionData['step']);
            $revision->setDueDate(new \DateTime($revisionData['due']));

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

        // Récupérer toutes les révisions dues aujourd'hui ou avant
        $today = new DateTime();
        $revisions = $revisionRepository->findDueFlashcardsByDeck($deck, $today);

        // Si aucune carte à réviser, afficher un message de fin
        if (!$revisions) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        // Trier les cartes selon leur date de révision
        usort($revisions, function ($a, $b) {
            return $a->getDueDate() <=> $b->getDueDate();
        });

        // Rediriger vers la session avec la première carte
        return $this->redirectToRoute('app_review_session', ['id' => $revisions[0]->getId()]);
    }

    #[Route('/review/session/{id<\d+>}', name: 'app_review_session')]
    public function session(Revision $revision): Response
    {
        // Vérifie que la révision appartient bien à un deck de l'utilisateur
        $deck = $revision->getFlashcard()->getDeck();
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }

        // Affiche la révision actuelle
        return $this->render('review/index.html.twig', [
            'revision' => $revision,
            'flashcard' => $revision->getFlashcard(),
        ]);
    }

    #[Route('/review/submit/{id<\d+>}', name: 'app_review_submit', methods: ['POST'])]
    public function submitReview(Revision $revision, Request $request): Response
    {
        // Récupérer la réponse de l'utilisateur
        $response = $request->request->get('response');

        if (!in_array($response, ['1', '2', '3', '4'])) {
            $this->addFlash('error', 'Réponse invalide.');
            return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
        }

        // Préparer les données pour l'API Flask
        $cardData = [
            'card_id' => $revision->getFlashcard()->getId(),
            'state' => $revision->getState(),
            'step' => $revision->getStep(),
            'stability' => $revision->getStability(),
            'difficulty' => $revision->getDifficulty(),
            'due' => $revision->getDueDate()->format(DATE_ISO8601),
            'last_review' => $revision->getLastReview() ? $revision->getLastReview()->format(DATE_ISO8601) : null,
        ];

        // Appeler l'API Flask pour mettre à jour la carte
        $result = $this->fsrsService->reviewCard($cardData, (int) $response);

        if (!$result || !isset($result['card'])) {
            $this->addFlash('error', 'Erreur lors de la mise à jour de la révision.');
            return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
        }

        // Mettre à jour les données de la révision avec les résultats de l'API
        $updatedCard = $result['card'];
        $revision->setState($updatedCard['state']);
        $revision->setStep($updatedCard['step']);
        $revision->setStability($updatedCard['stability']);
        $revision->setDifficulty($updatedCard['difficulty']);
        $revision->setDueDate(new \DateTime($updatedCard['due']));
        $revision->setLastReview(new \DateTime($updatedCard['last_review']));

        $this->entityManager->persist($revision);
        $this->entityManager->flush();

        // Rediriger vers la prochaine carte ou la fin
        return $this->redirectToRoute('app_review_start', ['deckId' => $revision->getFlashcard()->getDeck()->getId()]);
    }
}
