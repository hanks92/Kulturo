<?php

namespace App\Controller\Api;

use App\Repository\DeckRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class DeckController extends AbstractController
{
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
}
