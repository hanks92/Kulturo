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
     * Récupérer la prochaine carte à réviser pour un deck spécifique.
     * Inclut la logique liée à l'algorithme FSRS.
     *
     * @param Deck $deck
     * @return Revision|null
     */
    public function findNextRevisionForTodayByDeck(Deck $deck): ?Revision
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.flashcard', 'f') // Jointure avec Flashcard
            ->andWhere('f.deck = :deck') // Filtre par Deck
            ->andWhere('r.dueDate <= :now') // Filtre sur la date d'échéance (due date)
            ->setParameter('deck', $deck)
            ->setParameter('now', new \DateTime()) // Date actuelle
            ->orderBy('r.dueDate', 'ASC') // Trier par ordre croissant de dueDate
            ->setMaxResults(1) // Limiter à une révision
            ->getQuery()
            ->getOneOrNullResult(); // Retourner une révision ou null
    }

    /**
     * Récupérer toutes les cartes à réviser pour un deck spécifique aujourd'hui.
     * Peut être utilisé pour générer une vue complète des révisions à effectuer.
     *
     * @param Deck $deck
     * @return Revision[]
     */
    public function findAllRevisionsToReviewByDeck(Deck $deck): array
    {
        return $this->createQueryBuilder('r')
            ->innerJoin('r.flashcard', 'f') // Jointure avec Flashcard
            ->andWhere('f.deck = :deck') // Filtre par Deck
            ->andWhere('r.dueDate <= :now') // Filtre sur la date d'échéance (due date)
            ->setParameter('deck', $deck)
            ->setParameter('now', new \DateTime()) // Date actuelle
            ->orderBy('r.dueDate', 'ASC') // Trier par ordre croissant de dueDate
            ->getQuery()
            ->getResult(); // Retourner un tableau de révisions
    }

    /**
     * Préparer les révisions pour un deck spécifique.
     * Peut être utilisé pour définir un état ou préparer les cartes pour la révision.
     *
     * @param Deck $deck
     * @return int Le nombre de cartes affectées par la mise à jour.
     */
    public function prepareRevisionsForTodayByDeck(Deck $deck): int
    {
        return $this->createQueryBuilder('r')
            ->update()
            ->set('r.dueDate', ':now') // Exemple de mise à jour
            ->andWhere('r.dueDate <= :now') // Filtre sur la date d'échéance (due date)
            ->andWhere('r.flashcard IN (
                SELECT f.id FROM App\Entity\Flashcard f WHERE f.deck = :deck
            )') // Filtrer par Deck
            ->setParameter('deck', $deck)
            ->setParameter('now', new \DateTime()) // Date actuelle
            ->getQuery()
            ->execute(); // Exécuter la requête
    }
}
