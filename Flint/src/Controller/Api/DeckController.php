<?php

namespace App\Controller\Api;

use App\Entity\Deck;
use App\Repository\DeckRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class DeckController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/decks', name: 'decks', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getUserDecks(DeckRepository $deckRepository): JsonResponse
    {
        $user = $this->getUser();

        $decks = $deckRepository->findBy(['owner' => $user]);

        $data = array_map(fn($deck) => [
            'id' => $deck->getId(),
            'title' => $deck->getTitle(),
        ], $decks);

        return new JsonResponse($data);
    }

    #[Route('/decks', name: 'create_deck', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createDeck(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['title'])) {
            return new JsonResponse(['error' => 'Le titre est requis.'], 400);
        }

        $deck = $this->createDeckEntity($data['title']);

        return new JsonResponse([
            'id' => $deck->getId(),
            'title' => $deck->getTitle(),
        ], 201);
    }

    /**
     * ✅ Reprise de ta méthode stable de création de deck depuis ton ancien contrôleur.
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
