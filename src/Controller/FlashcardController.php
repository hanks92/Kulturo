<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Form\FlashcardType;
use App\Repository\FlashcardRepository;
use App\Repository\RevisionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FlashcardController extends AbstractController
{
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

            // Crée une révision associée avec des paramètres FSRS par défaut
            $revision = new Revision();
            $revision->setFlashcard($flashcard);
            $revision->setLastReview(new \DateTime()); // Aujourd'hui
            $revision->setDueDate((new \DateTime())->modify('+1 day')); // Premier intervalle : 1 jour
            $revision->setInterval(1); // Intervalle initial
            $revision->setStability(1.0); // Stabilité initiale
            $revision->setDifficulty(5.0); // Difficulté moyenne
            $revision->setRetrievability(0.9); // Probabilité de rappel initiale
            $revision->setState(1); // État Learning

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
