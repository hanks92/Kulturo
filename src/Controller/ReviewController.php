<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\Revision;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    #[Route('/review/{deckId}', name: 'app_review_start')]
    public function start(int $deckId, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérification de l'utilisateur connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupération du deck
        $deck = $entityManager->getRepository(Deck::class)->find($deckId);

        if (!$deck || $deck->getOwner() !== $user) {
            throw $this->createNotFoundException('Deck introuvable ou non autorisé.');
        }

        // Récupération des flashcards à réviser (nouvelle ou en retard)
        $revisions = $entityManager->getRepository(Revision::class)
            ->createQueryBuilder('r')
            ->join('r.flashcard', 'f')
            ->where('f.deck = :deck')
            ->andWhere('r.reviewDate <= :now OR r.status = :new')
            ->setParameter('deck', $deck)
            ->setParameter('now', new \DateTime())
            ->setParameter('new', 'new')
            ->getQuery()
            ->getResult();

        if (empty($revisions)) {
            $this->addFlash('info', 'Aucune carte à réviser pour ce deck.');
            return $this->redirectToRoute('app_dashboard'); // Redirige vers le tableau de bord ou un autre endroit
        }

        // Charger la première carte
        return $this->redirectToRoute('app_review_card', [
            'deckId' => $deckId,
            'revisionId' => $revisions[0]->getId(),
        ]);
    }

    #[Route('/review/{deckId}/card/{revisionId}', name: 'app_review_card')]
    public function card(int $deckId, int $revisionId, EntityManagerInterface $entityManager): Response
    {
        $revision = $entityManager->getRepository(Revision::class)->find($revisionId);

        if (!$revision) {
            throw $this->createNotFoundException('Révision introuvable.');
        }

        return $this->render('review/card.html.twig', [
            'revision' => $revision,
        ]);
    }

    #[Route('/review/{deckId}/card/{revisionId}/answer', name: 'app_review_answer', methods: ['POST'])]
    public function answer(
        int $deckId,
        int $revisionId,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $revision = $entityManager->getRepository(Revision::class)->find($revisionId);

        if (!$revision) {
            throw $this->createNotFoundException('Révision introuvable.');
        }

        $answer = $request->request->get('answer'); // Récupération de la réponse (correct/incorrect)

        // Mise à jour des attributs de révision
        if ($answer === 'correct') {
            $revision->setInterval($revision->getInterval() + 1); // Augmenter l'intervalle
            $revision->setEaseFactor($revision->getEaseFactor() + 0.1); // Exemple de mise à jour
            $revision->setStatus('reviewed'); // Mise à jour du statut
            $revision->setReviewDate((new \DateTime())->modify('+1 day')); // Prochaine révision
        } else {
            $revision->setInterval(1); // Réinitialiser l'intervalle
            $revision->setEaseFactor(max($revision->getEaseFactor() - 0.2, 1.3)); // Réduire le facteur de facilité
            $revision->setStatus('reviewed');
            $revision->setReviewDate((new \DateTime())->modify('+1 day')); // Revoir demain
        }

        $entityManager->flush();

        // Charger la prochaine carte ou terminer
        $nextRevision = $entityManager->getRepository(Revision::class)
            ->createQueryBuilder('r')
            ->join('r.flashcard', 'f')
            ->where('f.deck = :deck')
            ->andWhere('r.reviewDate <= :now OR r.status = :new')
            ->setParameter('deck', $deckId)
            ->setParameter('now', new \DateTime())
            ->setParameter('new', 'new')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($nextRevision) {
            return $this->redirectToRoute('app_review_card', [
                'deckId' => $deckId,
                'revisionId' => $nextRevision->getId(),
            ]);
        }

        $this->addFlash('success', 'Révision terminée pour ce deck !');
        return $this->redirectToRoute('app_dashboard'); // Fin de session
    }
}
