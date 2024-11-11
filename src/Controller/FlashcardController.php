<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Repository\FlashcardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FlashcardController extends AbstractController
{
    #[Route('/deck/{id}/review', name: 'flashcard_review')]
    public function review(Deck $deck, FlashcardRepository $flashcardRepository): Response
    {
        // Vérifie que le deck appartient à l'utilisateur connecté
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('You do not have access to this deck.');
        }

        // Récupère toutes les flashcards du deck
        $flashcards = $flashcardRepository->findBy(['deck' => $deck]);

        return $this->render('flashcard/review.html.twig', [
            'deck' => $deck,
            'flashcards' => $flashcards,
        ]);
    }
}
