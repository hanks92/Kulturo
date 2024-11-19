<?php

namespace App\Repository;

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
     * Récupérer la prochaine carte à réviser (si elle existe)
     */
    public function findNextFlashcardForToday(): ?Revision
    {
        return $this->createQueryBuilder('r')
            ->where('r.reviewDate <= :today') // Date de révision due aujourd'hui ou avant
            ->setParameter('today', new \DateTime()) // Date actuelle
            ->orderBy('r.reviewDate', 'ASC') // Trier par la date de révision la plus ancienne
            ->setMaxResults(1) // Récupérer une seule carte
            ->getQuery()
            ->getOneOrNullResult(); // Retourner un résultat ou null
    }

    /**
     * Récupérer toutes les cartes à réviser pour aujourd'hui
     *
     * @return Revision[] Retourne un tableau de révisions prêtes
     */
    public function findAllFlashcardsToReview(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.reviewDate <= :today') // Date de révision due aujourd'hui ou avant
            ->setParameter('today', new \DateTime()) // Date actuelle
            ->orderBy('r.reviewDate', 'ASC') // Trier par la date de révision la plus ancienne
            ->getQuery()
            ->getResult(); // Retourner un tableau de résultats
    }
}
