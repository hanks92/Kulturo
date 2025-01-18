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

        // Vérifie que le deck existe et appartient à l'utilisateur
        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        // Récupère la première révision due pour aujourd'hui
        $revision = $this->entityManager
            ->getRepository(Revision::class)
            ->findNextRevisionForTodayByDeck($deck);

        if (!$revision) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
    }

    #[Route('/review/session/{id<\d+>}', name: 'app_review_session')]
    public function session(Request $request, Revision $revision): Response
    {
        $deck = $revision->getFlashcard()->getDeck();
    
        // Vérifie que la révision appartient bien à un deck de l'utilisateur
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }
    
        // Si une réponse utilisateur est soumise
        if ($request->isMethod('POST')) {
            $userRating = $request->request->get('response');
    
            // Valide la réponse utilisateur
            if (!in_array($userRating, ['again', 'hard', 'good', 'easy'], true)) {
                $this->addFlash('error', 'Réponse invalide.');
                return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
            }
    
            /**
             * @var array{
             *     card_id: int|null,
             *     state: int|null,
             *     stability: float|null,
             *     difficulty: float|null,
             *     last_review: string|null,
             *     rating: string
             * } $revisionData
             */
            $revisionData = [
                'card_id' => $revision->getFlashcard()->getId(),
                'state' => $revision->getState(),
                'stability' => $revision->getStability(),
                'difficulty' => $revision->getDifficulty(),
                'last_review' => $revision->getLastReview()?->format('c'),
            ];
    
            try {
                // Appeler le service FSRS pour réviser la carte
                $updatedData = $this->fsrsService->reviewCard($revisionData, $userRating);
    
                // Mettre à jour la révision avec les données renvoyées par FSRS
                $revision->setStability($updatedData['stability']);
                $revision->setDifficulty($updatedData['difficulty']);
                $revision->setLastReview(new \DateTime($updatedData['last_review']));
                $revision->setDueDate(new \DateTime($updatedData['due']));
                $revision->setState($updatedData['state']);
    
                $this->entityManager->persist($revision);
                $this->entityManager->flush();
    
                // Récupérer la prochaine révision
                $nextRevision = $this->entityManager
                    ->getRepository(Revision::class)
                    ->findNextRevisionForTodayByDeck($deck);
    
                if (!$nextRevision) {
                    return $this->render('review/finished.html.twig', [
                        'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                        'deck' => $deck,
                    ]);
                }
    
                return $this->redirectToRoute('app_review_session', ['id' => $nextRevision->getId()]);
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
            ->findNextRevisionForTodayByDeck($deck);

        if (!$revision) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck,
            ]);
        }

        return $this->render('review/index.html.twig', [
            'revision' => $revision,
        ]);
    }
}
