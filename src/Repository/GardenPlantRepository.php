<?php

namespace App\Repository;

use App\Entity\GardenPlant;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GardenPlant>
 */
class GardenPlantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GardenPlant::class);
    }

    public function deleteAllByUser(User $user): void
    {
        $this->createQueryBuilder('p')
            ->delete()
            ->where('p.userApp = :user') // ✅ Correction ici
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }


    //    /**
    //     * @return GardenPlant[] Returns an array of GardenPlant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?GardenPlant
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
