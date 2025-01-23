<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Form\FlashcardType;
use App\Repository\FlashcardRepository;
use App\Repository\RevisionRepository;
use App\Service\FSRSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FlashcardController extends AbstractController
{
    private FSRSService $fsrsService;

    public function __construct(FSRSService $fsrsService)
    {
        $this->fsrsService = $fsrsService;
    }

    #[Route('/deck/{id}/review', name: 'flashcard_review')]
    public function review(Deck $deck, RevisionRepository $revisionRepository): Response
    {
        // Vérifie que le deck appartient à l'utilisateur connecté
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('You do not have access to this deck.');
        }

        // Récupère les révisions dues pour aujourd'hui ou avant
        $today = new \DateTime();
        $revisions = $revisionRepository->findDueRevisionsForDeck($deck, $today);

        return $this->render('flashcard/review.html.twig', [
            'deck' => $deck,
            'revisions' => $revisions, // Révisions dues, associées aux flashcards
        ]);
    }

    #[Route('/deck/{id}/flashcard/create', name: 'flashcard_create')]
    public function create(Deck $deck, Request $request, EntityManagerInterface $entityManager): Response
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
            $entityManager->persist($flashcard);

            // Initialisation de la révision avec FSRS
            $fsrsDefaults = $this->fsrsService->initializeCard();

            // Crée une révision associée avec des paramètres FSRS
            $revision = new Revision();
            $revision->setFlashcard($flashcard);
            $revision->setLastReview(new \DateTime()); // Aujourd'hui
            $revision->setDueDate(new \DateTime($fsrsDefaults['due'])); // Date due calculée
            $revision->setInterval($fsrsDefaults['interval']); // Intervalle initial
            $revision->setStability($fsrsDefaults['stability']); // Stabilité initiale
            $revision->setDifficulty($fsrsDefaults['difficulty']); // Difficulté initiale
            $revision->setRetrievability($fsrsDefaults['retrievability']); // Probabilité de rappel initiale
            $revision->setState($fsrsDefaults['state']); // État initial
            $revision->setStep($fsrsDefaults['step']); // Étape initiale

            // Persist la révision
            $entityManager->persist($revision);

            // Sauvegarde les deux entités
            $entityManager->flush();

            // Redirige vers la page de création pour ajouter une nouvelle flashcard
            return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
        }

        return $this->render('flashcard/create.html.twig', [
            'form' => $form->createView(),
            'deck' => $deck,
        ]);
    }
}
