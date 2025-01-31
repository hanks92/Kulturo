<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Flashcard;
use App\Entity\Revision;
use App\Form\FlashcardType;
use App\Repository\RevisionRepository;
use App\Service\FSRSService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use DateTime;

class ReviewController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private FSRSService $fsrsService;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager, FSRSService $fsrsService, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->fsrsService = $fsrsService;
        $this->logger = $logger;
    }

    #[Route('/deck/{id}/flashcard/create', name: 'flashcard_create')]
    public function createFlashcard(Deck $deck, Request $request): Response
    {
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Vous n\'avez pas accès à ce deck.');
        }

        $flashcard = new Flashcard();
        $flashcard->setDeck($deck);

        $form = $this->createForm(FlashcardType::class, $flashcard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($flashcard);
            $this->entityManager->flush();

            $this->logger->info('✅ Flashcard créée', ['id' => $flashcard->getId()]);

            // Récupération des données FSRS
            $revisionData = $this->fsrsService->initializeCard($flashcard->getId());

            if (!$revisionData) {
                $this->logger->error('❌ Échec de l\'initialisation FSRS.');
                $this->addFlash('error', 'Échec de l\'initialisation de la révision via FSRS.');
                return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
            }

            $this->logger->info('🟡 Données FSRS reçues', ['data' => $revisionData]);

            // Vérification et attribution des valeurs (accepte NULL)
            $revision = new Revision();
            $revision->setFlashcard($flashcard);
            $revision->setStability($revisionData['stability'] ?? null);
            $revision->setRetrievability($revisionData['retrievability'] ?? 0);
            $revision->setDifficulty($revisionData['difficulty'] ?? null);
            $revision->setState($revisionData['state'] ?? 1);
            $revision->setStep($revisionData['step'] ?? 0);
            $revision->setDueDate(new DateTime($revisionData['due'] ?? 'now'));

            // Sauvegarde en base
            $this->entityManager->persist($revision);
            $this->entityManager->flush();

            $this->logger->info('✅ Révision initialisée', ['revision_id' => $revision->getId()]);

            $this->addFlash('success', 'Flashcard et révision créées avec succès !');

            return $this->redirectToRoute('flashcard_create', ['id' => $deck->getId()]);
        }

        return $this->render('flashcard/create.html.twig', [
            'form' => $form->createView(),
            'deck' => $deck,
        ]);
    }

    #[Route('/review/start/{deckId}', name: 'app_review_start')]
    public function start(int $deckId, RevisionRepository $revisionRepository): Response
    {
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        $today = new DateTime();
        $revisions = $revisionRepository->findDueFlashcardsByDeck($deck, $today);

        if (!$revisions) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        return $this->redirectToRoute('app_review_session', ['id' => $revisions[0]->getId()]);
    }

    #[Route('/review/session/{id}', name: 'app_review_session')]
    public function session(Revision $revision): Response
    {
        $deck = $revision->getFlashcard()->getDeck();
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }

        return $this->render('review/index.html.twig', [
            'revision' => $revision,
            'flashcard' => $revision->getFlashcard(),
        ]);
    }

    #[Route('/review/submit/{id}', name: 'app_review_submit', methods: ['POST'])]
    public function submitReview(Revision $revision, Request $request, RevisionRepository $revisionRepository): Response
    {
        $response = $request->request->get('response');

        $ratingMapping = ['1' => 1, '2' => 2, '3' => 3, '4' => 4];

        if (!isset($ratingMapping[$response])) {
            $this->addFlash('error', 'Réponse invalide.');
            return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
        }

        $cardData = [
            'card_id' => $revision->getFlashcard()->getId(),
            'state' => $revision->getState(),
            'step' => $revision->getStep(),
            'stability' => $revision->getStability(),
            'difficulty' => $revision->getDifficulty(),
            'due' => $revision->getDueDate()?->format('Y-m-d\TH:i:s\Z'),
            'last_review' => $revision->getLastReview()?->format('Y-m-d\TH:i:s\Z'),
        ];

        $updatedData = $this->fsrsService->updateCard($cardData, $ratingMapping[$response]);

        if (!$updatedData || !isset($updatedData['card'])) {
            $this->addFlash('error', 'Erreur lors de la mise à jour de la révision.');
            return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
        }

        $updatedCard = $updatedData['card'];
        $revision->setState($updatedCard['state']);
        $revision->setStep($updatedCard['step']);
        $revision->setStability($updatedCard['stability'] ?? null);
        $revision->setDifficulty($updatedCard['difficulty'] ?? null);
        $revision->setDueDate(new DateTime($updatedCard['due'] ?? 'now'));
        $revision->setLastReview(new DateTime($updatedCard['last_review'] ?? 'now'));

        $this->entityManager->persist($revision);
        $this->entityManager->flush();

        $this->logger->info('✅ Révision mise à jour', ['revision_id' => $revision->getId()]);

        $nextRevision = $revisionRepository->findNextFlashcardForTodayByDeck($revision->getFlashcard()->getDeck());

        if (!$nextRevision) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour aujourd\'hui !',
            ]);
        }

        return $this->redirectToRoute('app_review_session', ['id' => $nextRevision->getId()]);
    }
}
