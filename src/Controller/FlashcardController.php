<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Form\FlashcardType;
use App\Repository\FlashcardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            $entityManager->persist($flashcard);
            $entityManager->flush();

            return $this->redirectToRoute('deck_show', ['id' => $deck->getId()]);
        }

        return $this->render('flashcard/create.html.twig', [
            'form' => $form->createView(),
            'deck' => $deck,
        ]);
    }
}
