<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Form\FlashcardType;
use App\Repository\FlashcardRepository;
use App\Service\FSRSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FlashcardController extends AbstractController
{
    private FSRSService $fsrsService;
    private EntityManagerInterface $entityManager;

    public function __construct(FSRSService $fsrsService, EntityManagerInterface $entityManager)
    {
        $this->fsrsService = $fsrsService;
        $this->entityManager = $entityManager;
    }

    #[Route('/deck/{id}/review', name: 'flashcard_review')]
    public function review(Deck $deck, FlashcardRepository $flashcardRepository): Response
    {
        // Vérification de l'accès au deck
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce deck.');
        }

        // Récupération des flashcards du deck
        $flashcards = $flashcardRepository->findBy(['deck' => $deck]);

        return $this->render('flashcard/review.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards,
        ]);
    }

    #[Route('/deck/{id}/flashcard/create', name: 'flashcard_create')]
    public function create(Deck $deck, Request $request): Response
    {
        // Vérification de l'accès au deck
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce deck.');
        }

        // Création et association de la flashcard au deck
        $flashcard = new Flashcard();
        $flashcard->setDeck($deck);

        $form = $this->createForm(FlashcardType::class, $flashcard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persistance de la flashcard
            $this->entityManager->persist($flashcard);
            $this->entityManager->flush();

            // Initialisation FSRS
            $this->initializeFSRS($flashcard);

            // Redirection après création
            $this->addFlash('success', 'Flashcard créée avec succès !');
            return $this->redirectToRoute('flashcard_review', ['id' => $deck->getId()]);
        }

        return $this->render('flashcard/create.html.twig', [
            'form' => $form->createView(),
            'deck' => $deck,
        ]);
    }

    #[Route('/deck/{id}/flashcards', name: 'flashcard_list')]
    public function list(Deck $deck, FlashcardRepository $flashcardRepository): Response
        {
            // Vérification que l'utilisateur possède bien le deck
            if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce deck.');
        }

    // Récupération des flashcards associées au deck
    $flashcards = $flashcardRepository->findBy(['deck' => $deck]);

            return $this->render('deck/list.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards,
         ]);
    }


    /**
     * 📌 Fonction pour créer et persister une flashcard (réutilisable par l'IA et le formulaire)
     */
    public function createFlashcard(Deck $deck, string $question, string $answer): Flashcard
    {
        $flashcard = new Flashcard();
        $flashcard->setDeck($deck);
        $flashcard->setQuestion($question);
        $flashcard->setAnswer($answer);

        $this->entityManager->persist($flashcard);
        $this->entityManager->flush();

        // Initialisation FSRS
        $this->initializeFSRS($flashcard);

        return $flashcard;
    }

    /**
     * 📌 Fonction privée pour initialiser FSRS après la création d'une flashcard
     */
    private function initializeFSRS(Flashcard $flashcard): void
    {
        $revisionData = $this->fsrsService->initializeCard($flashcard->getId());

        if ($revisionData) {
            $revision = new Revision();
            $revision->setFlashcard($flashcard);
            $revision->setStability($revisionData['stability'] ?? null);
            $revision->setDifficulty($revisionData['difficulty'] ?? null);
            $revision->setState($revisionData['state']);
            $revision->setStep($revisionData['step']);
            $revision->setDueDate(new \DateTime($revisionData['due']));

            $this->entityManager->persist($revision);
            $this->entityManager->flush();
        }
    }
}
