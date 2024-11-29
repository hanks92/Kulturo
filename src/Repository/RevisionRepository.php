<?php

namespace App\Repository;

use App\Entity\Deck;
use App\Entity\Revision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Revision>
 */
class RevisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Revision::class);
    }

    /**
     * Récupérer la prochaine carte à réviser pour un deck spécifique
     */
    public function findNextFlashcardForTodayByDeck(Deck $deck): ?Revision
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.flashcard', 'f') // Jointure avec Flashcard
            ->andWhere('f.deck = :deck') // Filtre par Deck
            ->andWhere('r.reviewDate <= :today') // Filtre sur la date de révision
            ->setParameter('deck', $deck)
            ->setParameter('today', new \DateTime()) // Date actuelle
            ->orderBy('r.reviewDate', 'ASC') // Trier par la date de révision
            ->setMaxResults(1) // Limiter à une révision
            ->getQuery()
            ->getOneOrNullResult(); // Retourner une révision ou null
    }

    /**
     * Récupérer toutes les cartes à réviser pour un deck spécifique
     */
    public function findAllFlashcardsToReviewByDeck(Deck $deck): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.flashcard', 'f') // Jointure avec Flashcard
            ->andWhere('f.deck = :deck') // Filtre par Deck
            ->andWhere('r.reviewDate <= :today') // Filtre sur la date de révision
            ->setParameter('deck', $deck)
            ->setParameter('today', new \DateTime()) // Date actuelle
            ->orderBy('r.reviewDate', 'ASC') // Trier par la date de révision
            ->getQuery()
            ->getResult(); // Retourner un tableau de révisions
    }
}
