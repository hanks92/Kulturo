<?php

namespace App\Controller\Api;

use App\Entity\Deck;
use App\Entity\Revision;
use App\Entity\ReviewLog;
use App\Repository\RevisionRepository;
use App\Service\FSRSService;
use App\Service\StatsUpdater;
use App\Service\AchievementUnlocker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use DateTime;

#[Route('/api/review')]
class ApiReviewController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FSRSService $fsrsService;
    private StatsUpdater $statsUpdater;
    private AchievementUnlocker $achievementUnlocker;

    public function __construct(
        EntityManagerInterface $entityManager,
        FSRSService $fsrsService,
        StatsUpdater $statsUpdater,
        AchievementUnlocker $achievementUnlocker
    ) {
        $this->entityManager = $entityManager;
        $this->fsrsService = $fsrsService;
        $this->statsUpdater = $statsUpdater;
        $this->achievementUnlocker = $achievementUnlocker;
    }

    #[Route('/start/{deckId}', name: 'api_review_start', methods: ['GET'])]
    public function start(int $deckId, RevisionRepository $revisionRepository): JsonResponse
    {
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'Deck introuvable ou accès refusé.'], 403);
        }

        $revisions = $revisionRepository->findDueFlashcardsByDeck($deck, new \DateTime());

        if (empty($revisions)) {
            return $this->json(['firstRevisionId' => null]);
        }

        return $this->json(['firstRevisionId' => $revisions[0]->getId()]);
    }

    #[Route('/session/{id}', name: 'api_review_session', methods: ['GET'])]
    public function session(Revision $revision): JsonResponse
    {
        $deck = $revision->getFlashcard()->getDeck();
        if ($deck->getOwner() !== $this->getUser()) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $predictedDueDates = [];

        foreach ([1, 2, 3, 4] as $rating) {
            $simulation = $this->fsrsService->updateCard([
                'card_id' => $revision->getFlashcard()->getId(),
                'state' => $revision->getState(),
                'step' => $revision->getStep(),
                'stability' => $revision->getStability(),
                'difficulty' => $revision->getDifficulty(),
                'due' => $revision->getDueDate()?->format('Y-m-d\TH:i:s\Z'),
                'last_review' => $revision->getLastReview()?->format('Y-m-d\TH:i:s\Z'),
            ], $rating);

            if (isset($simulation['updated_card']['due'])) {
                $dueDate = new \DateTime($simulation['updated_card']['due']);
                $predictedDueDates[$rating] = $dueDate->format('d/m/Y');
            } else {
                $predictedDueDates[$rating] = 'N/A';
            }
        }

        return $this->json([
            'flashcard' => [
                'question' => $revision->getFlashcard()->getQuestion(),
                'answer' => $revision->getFlashcard()->getAnswer(),
            ],
            'predictedDueDates' => $predictedDueDates,
        ]);
    }

    #[Route('/submit/{id}', name: 'api_review_submit', methods: ['POST'])]
    public function submit(
        Revision $revision,
        Request $request,
        RevisionRepository $revisionRepository
    ): JsonResponse {
        $response = $request->request->get('response');
        $ratingMapping = ['1' => 1, '2' => 2, '3' => 3, '4' => 4];

        if (!isset($ratingMapping[$response])) {
            return $this->json(['error' => 'Réponse invalide'], Response::HTTP_BAD_REQUEST);
        }

        $rating = $ratingMapping[$response];

        $updatedData = $this->fsrsService->updateCard([
            'card_id' => $revision->getFlashcard()->getId(),
            'state' => $revision->getState(),
            'step' => $revision->getStep(),
            'stability' => $revision->getStability(),
            'difficulty' => $revision->getDifficulty(),
            'due' => $revision->getDueDate()?->format('Y-m-d\TH:i:s\Z'),
            'last_review' => $revision->getLastReview()?->format('Y-m-d\TH:i:s\Z'),
        ], $rating);

        if (!$updatedData || !isset($updatedData['updated_card']) || !isset($updatedData['review_log'])) {
            return $this->json(['error' => 'Erreur dans le calcul FSRS'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $updatedCard = $updatedData['updated_card'];
        $reviewLogData = $updatedData['review_log'];

        $revision->setState($updatedCard['state']);
        $revision->setStep($updatedCard['step']);
        $revision->setStability($updatedCard['stability'] ?? null);
        $revision->setDifficulty($updatedCard['difficulty'] ?? null);
        $revision->setDueDate(new DateTime($updatedCard['due'] ?? 'now'));
        $revision->setLastReview(new DateTime($updatedCard['last_review'] ?? 'now'));

        $reviewLog = new ReviewLog();
        $reviewLog->setRevision($revision);
        $reviewLog->setRating($reviewLogData['rating']);
        $reviewLog->setReviewDateTime(new DateTime($reviewLogData['review_datetime']));
        $reviewLog->setReviewDuration($reviewLogData['review_duration'] ?? null);

        $this->entityManager->persist($reviewLog);
        $this->entityManager->persist($revision);

        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $this->statsUpdater->updateStreak($user);

        $stats = $user->getStats();
        $stats->setTotalXp(($stats->getTotalXp() ?? 0) + 1);
        $stats->setWater(($stats->getWater() ?? 0) + 1);
        $stats->setCardsReviewed(($stats->getCardsReviewed() ?? 0) + 1);
        $this->entityManager->persist($stats);

        $this->entityManager->flush();

        $nextRevisions = $revisionRepository->findDueFlashcardsByDeck($revision->getFlashcard()->getDeck(), new \DateTime());

        $this->achievementUnlocker->unlock($user, 'session_complete');
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'nextRevisionId' => $nextRevisions[0]->getId() ?? null,
        ]);
    }
}
