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
     * Récupérer la prochaine carte à réviser pour un deck spécifique (une seule révision)
     */
    public function findNextFlashcardForTodayByDeck(Deck $deck): ?Revision
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.flashcard', 'f') // Jointure avec Flashcard
            ->andWhere('f.deck = :deck') // Filtre par Deck
            ->andWhere('r.dueDate <= :today') // Filtre sur la date d'échéance (dueDate)
            ->setParameter('deck', $deck)
            ->setParameter('today', (new \DateTime())->setTime(0, 0, 0)) // Date actuelle avec l'heure à minuit
            ->orderBy('r.dueDate', 'ASC') // Trier par la date d'échéance
            ->setMaxResults(1) // Limiter à une révision
            ->getQuery()
            ->getOneOrNullResult(); // Retourner une révision ou null
    }

    /**
     * Récupérer toutes les cartes à réviser pour un deck spécifique (toutes les révisions dues)
     */
    public function findDueFlashcardsByDeck(Deck $deck, \DateTime $today): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.flashcard', 'f') // Jointure avec Flashcard
            ->andWhere('f.deck = :deck') // Filtre par Deck
            ->andWhere('r.dueDate <= :today') // Filtre sur la date d'échéance (dueDate)
            ->setParameter('deck', $deck)
            ->setParameter('today', $today) // Date actuelle passée en paramètre
            ->orderBy('r.dueDate', 'ASC') // Trier par la date d'échéance
            ->getQuery()
            ->getResult(); // Retourner toutes les révisions
    }
}
