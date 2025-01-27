<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Form\FlashcardType;
use App\Repository\FlashcardRepository;
use App\Service\FSRSService; // Service pour appeler l'API Flask
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
        // Vérifie que le deck appartient à l'utilisateur connecté
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('You do not have access to this deck.');
        }

        // Récupère les flashcards associées au deck
        $flashcards = $flashcardRepository->findBy(['deck' => $deck]);

        return $this->render('flashcard/review.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards,
        ]);
    }

    #[Route('/deck/{id}/flashcard/create', name: 'flashcard_create')]
    public function create(Deck $deck, Request $request): Response
    {
        // Vérifie que le deck appartient à l'utilisateur connecté
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('You do not have access to this deck.');
        }

        // Crée une nouvelle flashcard et l’associe au deck
        $flashcard = new Flashcard();
        $flashcard->setDeck($deck);

        $form = $this->createForm(FlashcardType::class, $flashcard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist la flashcard
            $this->entityManager->persist($flashcard);
            $this->entityManager->flush();

            // Appel à l'API Flask pour initialiser les paramètres FSRS
            $revisionData = $this->fsrsService->initializeCard($flashcard->getId());

            if (!$revisionData) {
                $this->addFlash('error', 'Failed to initialize flashcard using FSRS.');
                return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
            }

            // Crée une révision associée
            $revision = new Revision();
            $revision->setFlashcard($flashcard);
            $revision->setStability($revisionData['stability'] ?? null);
            $revision->setDifficulty($revisionData['difficulty'] ?? null);
            $revision->setState($revisionData['state']);
            $revision->setStep($revisionData['step']);
            $revision->setDueDate(new \DateTime($revisionData['due']));

            // Persist la révision
            $this->entityManager->persist($revision);
            $this->entityManager->flush();

            // Redirige l'utilisateur avec un message de succès
            $this->addFlash('success', 'Flashcard created and initialized successfully!');
            return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
        }

        return $this->render('flashcard/create.html.twig', [
            'form' => $form->createView(),
            'deck' => $deck,
        ]);
    }
}
