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
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/deck', name: 'app_deck')]
    public function index(DeckRepository $deckRepository): Response
    {
        $user = $this->getUser();
        $decks = $deckRepository->findBy(['owner' => $user]);

        return $this->render('deck/index.html.twig', [
            'decks' => $decks,
        ]);
    }

    #[Route('/deck/create', name: 'deck_create')]
    public function create(Request $request): Response
    {
        $deck = new Deck();
        $form = $this->createForm(DeckType::class, $deck);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation de la nouvelle méthode pour créer et persister le deck
            $this->createDeckEntity($deck->getTitle());

            return $this->redirectToRoute('app_deck');
        }

        return $this->render('deck/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deck/{id}', name: 'deck_show')]
    public function show(Deck $deck): Response
    {
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck not found or you do not have access to it.');
        }

        return $this->render('deck/show.html.twig', [
            'deck' => $deck,
        ]);
    }

    /**
     * 📌 Fonction pour créer un Deck et le persister en base de données.
     * Cette fonction est utilisée aussi bien dans la création manuelle que dans l'IA.
     */
    public function createDeckEntity(string $title): Deck
    {
        $deck = new Deck();
        $deck->setTitle($title);
        $deck->setOwner($this->getUser());
        $deck->setCreatedAt(new \DateTimeImmutable());
        $deck->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($deck);
        $this->entityManager->flush();

        return $deck;
    }
}
