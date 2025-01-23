<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Revision;
use App\Service\FSRSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FSRSService $fsrsService;

    public function __construct(EntityManagerInterface $entityManager, FSRSService $fsrsService)
    {
        $this->entityManager = $entityManager;
        $this->fsrsService = $fsrsService;
    }

    #[Route('/review/start/{deckId<\d+>}', name: 'app_review_start')]
    public function start(int $deckId): Response
    {
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        // Récupère les révisions dues pour aujourd'hui
        $revisions = $this->entityManager
            ->getRepository(Revision::class)
            ->findRevisionsDueForTodayByDeck($deck);

        if (empty($revisions)) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        // Redirige vers la première révision
        return $this->redirectToRoute('app_review_session', ['id' => $revisions[0]->getId()]);
    }

    #[Route('/review/session/{id<\d+>}', name: 'app_review_session', methods: ['GET', 'POST'])]
    public function session(Request $request, Revision $revision): Response
    {
        $deck = $revision->getFlashcard()->getDeck();

        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }

        if ($request->isMethod('POST')) {
            $userRating = $request->request->get('response');

            if (!in_array($userRating, ['again', 'hard', 'good', 'easy'], true)) {
                $this->addFlash('error', 'Réponse invalide.');
                return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
            }

            $revisionData = [
                'card_id' => $revision->getFlashcard()->getId(),
                'state' => $revision->getState(),
                'stability' => $revision->getStability(),
                'difficulty' => $revision->getDifficulty(),
                'last_review' => $revision->getLastReview()?->format('c'),
                'step' => $revision->getStep(),
            ];

            try {
                $updatedData = $this->fsrsService->reviewCard($revisionData, $userRating);

                $revision->setStability($updatedData['stability']);
                $revision->setDifficulty($updatedData['difficulty']);
                $revision->setLastReview(new \DateTime($updatedData['last_review']));
                $revision->setDueDate(new \DateTime($updatedData['due']));
                $revision->setState($updatedData['state']);
                $revision->setStep($updatedData['step']);

                $this->entityManager->persist($revision);
                $this->entityManager->flush();

                $nextRevision = $this->entityManager
                    ->getRepository(Revision::class)
                    ->findRevisionsDueForTodayByDeck($deck);

                if (empty($nextRevision)) {
                    return $this->render('review/finished.html.twig', [
                        'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                        'deck' => $deck,
                    ]);
                }

                return $this->redirectToRoute('app_review_session', ['id' => $nextRevision[0]->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la communication avec FSRS : ' . $e->getMessage());
            }
        }

        return $this->render('review/index.html.twig', [
            'revision' => $revision,
            'flashcard' => $revision->getFlashcard(),
        ]);
    }

    #[Route('/review/{deckId<\d+>}', name: 'app_review')]
    public function index(int $deckId): Response
    {
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        $revision = $this->entityManager
            ->getRepository(Revision::class)
            ->findRevisionsDueForTodayByDeck($deck);

        if (empty($revision)) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        return $this->render('review/index.html.twig', [
            'revision' => $revision[0],
        ]);
    }
}
