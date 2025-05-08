<?php

namespace App\Controller\Api;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Repository\FlashcardRepository;
use App\Service\FSRSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class ApiFlashcardController extends AbstractController
{
    private FSRSService $fsrsService;
    private EntityManagerInterface $entityManager;

    public function __construct(FSRSService $fsrsService, EntityManagerInterface $entityManager)
    {
        $this->fsrsService = $fsrsService;
        $this->entityManager = $entityManager;
    }

    #[Route('/deck/{id}/flashcards', name: 'flashcard_list', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(Deck $deck, FlashcardRepository $flashcardRepository): JsonResponse
    {
        if ($deck->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $flashcards = $flashcardRepository->findBy(['deck' => $deck]);

        $data = array_map(fn($fc) => [
            'id' => $fc->getId(),
            'question' => $fc->getQuestion(),
            'answer' => $fc->getAnswer(),
        ], $flashcards);

        return $this->json($data);
    }

    #[Route('/deck/{id}/flashcard', name: 'flashcard_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(Deck $deck, Request $request): JsonResponse
    {
        if ($deck->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $question = $data['question'] ?? null;
        $answer = $data['answer'] ?? null;

        if (!$question || !$answer) {
            return $this->json(['error' => 'Missing question or answer'], 400);
        }

        $flashcard = new Flashcard();
        $flashcard->setDeck($deck);
        $flashcard->setQuestion($question);
        $flashcard->setAnswer($answer);

        $this->entityManager->persist($flashcard);
        $this->entityManager->flush();

        $this->initializeFSRS($flashcard);

        return $this->json([
            'id' => $flashcard->getId(),
            'message' => 'Flashcard créée avec succès !'
        ], 201);
    }

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
