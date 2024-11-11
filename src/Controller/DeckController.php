<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Form\DeckType;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeckController extends AbstractController
{
    #[Route('/deck', name: 'app_deck')]
    public function index(DeckRepository $deckRepository): Response
    {
        // Récupère les decks de l'utilisateur connecté
        $user = $this->getUser();
        $decks = $deckRepository->findBy(['owner' => $user]);

        return $this->render('deck/index.html.twig', [
            'decks' => $decks,
        ]);
    }

    #[Route('/deck/{id}', name: 'deck_show')]
    public function show(int $id, DeckRepository $deckRepository): Response
    {
        // Récupère le deck par son ID
        $deck = $deckRepository->find($id);

        // Vérifie que le deck existe et appartient à l'utilisateur connecté
        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck not found or you do not have access to it.');
        }

        return $this->render('deck/show.html.twig', [
            'deck' => $deck,
        ]);
    }

    #[Route('/deck/create', name: 'deck_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $deck = new Deck();
        $form = $this->createForm(DeckType::class, $deck);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $deck->setOwner($this->getUser());  // Associe le deck à l'utilisateur connecté
            $deck->setCreatedAt(new \DateTimeImmutable());
            $deck->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->persist($deck);
            $entityManager->flush();

            return $this->redirectToRoute('app_deck'); // Redirige vers la liste des decks
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
