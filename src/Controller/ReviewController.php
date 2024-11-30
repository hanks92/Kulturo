<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Revision;
use App\Service\SM2Algorithm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SM2Algorithm $sm2Algorithm;

    public function __construct(EntityManagerInterface $entityManager, SM2Algorithm $sm2Algorithm)
    {
        $this->entityManager = $entityManager;
        $this->sm2Algorithm = $sm2Algorithm;
    }

    #[Route('/review/start/{deckId<\d+>}', name: 'app_review_start')]
    public function start(int $deckId): Response
    {
        // Récupérer le Deck
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        // Vérifier si le Deck existe et appartient à l'utilisateur
        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        // Récupérer la première carte à réviser pour ce deck
        $flashcard = $this->entityManager
            ->getRepository(Revision::class)
            ->findNextFlashcardForTodayByDeck($deck);

        // Si aucune carte à réviser, afficher un message de fin
        if (!$flashcard) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck, // Ajout de deck ici
            ]);
        }

        // Rediriger vers la session avec la première carte
        return $this->redirectToRoute('app_review_session', ['id' => $flashcard->getId()]);
    }

    #[Route('/review/session/{id<\d+>}', name: 'app_review_session')]
    public function session(Request $request, Revision $revision): Response
    {
        // Vérifier que la flashcard appartient bien à un deck de l'utilisateur
        $deck = $revision->getFlashcard()->getDeck();
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }

        // Si une réponse est soumise (méthode POST)
        if ($request->isMethod('POST')) {
            // Récupérer la réponse utilisateur (facile, correct, difficile, à revoir)
            $response = $request->request->get('response');

            // Vérifier la validité de la réponse
            if (!in_array($response, ['facile', 'correct', 'difficile', 'a_revoir'])) {
                $this->addFlash('error', 'Réponse invalide.');
                return $this->redirectToRoute('app_review_session', ['id' => $revision->getId()]);
            }

            // Calculer les nouvelles valeurs avec SM-2
            $result = $this->sm2Algorithm->calculateNextReview(
                $revision->getEaseFactor(),
                $revision->getInterval(),
                $response
            );

            // Mettre à jour l'entité Revision
            $revision->setEaseFactor($result['easeFactor']);
            $revision->setInterval($result['interval']);
            $revision->setReviewDate($result['nextReviewDate']);

            // Sauvegarder les changements dans la base de données
            $this->entityManager->persist($revision);
            $this->entityManager->flush();

            // Récupérer la prochaine carte
            $nextRevision = $this->entityManager
                ->getRepository(Revision::class)
                ->findNextFlashcardForTodayByDeck($deck);

            // Si aucune carte suivante, afficher un message de fin
            if (!$nextRevision) {
                return $this->render('review/finished.html.twig', [
                    'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                    'deck' => $deck, // Ajout de deck ici
                ]);
            }

            // Rediriger vers la prochaine carte
            return $this->redirectToRoute('app_review_session', ['id' => $nextRevision->getId()]);
        }

        // Afficher la carte actuelle
        return $this->render('review/index.html.twig', [
            'revision' => $revision,
            'flashcard' => $revision->getFlashcard(),
        ]);
    }

    #[Route('/review/{deckId<\d+>}', name: 'app_review')]
    public function index(int $deckId): Response
    {
        // Récupérer le Deck
        $deck = $this->entityManager->getRepository(Deck::class)->find($deckId);

        // Vérifier si le Deck existe et appartient à l'utilisateur
        if (!$deck || $deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Deck introuvable ou accès refusé.');
        }

        // Récupérer la prochaine carte à réviser pour ce deck
        $nextRevision = $this->entityManager
            ->getRepository(Revision::class)
            ->findNextFlashcardForTodayByDeck($deck);

        // Si aucune carte à réviser, afficher un message de fin
        if (!$nextRevision) {
            return $this->render('review/finished.html.twig', [
                'message' => 'Toutes les cartes ont été révisées pour ce deck aujourd\'hui !',
                'deck' => $deck, // Ajout de deck ici
            ]);
        }

        // Rendre la vue pour afficher la carte
        return $this->render('review/index.html.twig', [
            'revision' => $nextRevision,
        ]);
    }

    #[Route('/review/{id<\d+>}/response', name: 'app_review_response', methods: ['POST'])]
    public function handleResponse(Request $request, Revision $revision): Response
    {
        // Vérifier que la flashcard appartient bien à un deck de l'utilisateur
        $deck = $revision->getFlashcard()->getDeck();
        if ($deck->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException('Révision non autorisée.');
        }

        // Récupérer la réponse utilisateur (facile, correct, difficile, à revoir)
        $response = $request->request->get('response');

        // Vérifier la validité de la réponse
        if (!in_array($response, ['facile', 'correct', 'difficile', 'a_revoir'])) {
            $this->addFlash('error', 'Réponse invalide.');
            return $this->redirectToRoute('app_review', ['deckId' => $deck->getId()]);
        }

        // Récupérer les attributs actuels de la révision
        $easeFactor = $revision->getEaseFactor();
        $interval = $revision->getInterval();

        // Calculer les nouvelles valeurs avec l'algorithme SM-2
        $result = $this->sm2Algorithm->calculateNextReview($easeFactor, $interval, $response);

        // Mettre à jour l'entité Revision
        $revision->setEaseFactor($result['easeFactor']);
        $revision->setInterval($result['interval']);
        $revision->setReviewDate($result['nextReviewDate']);

        // Sauvegarder les changements
        $this->entityManager->persist($revision);
        $this->entityManager->flush();

        $this->addFlash('success', 'Révision mise à jour avec succès.');

        // Rediriger vers la prochaine carte ou la fin des révisions
        return $this->redirectToRoute('app_review', ['deckId' => $deck->getId()]);
    }
}