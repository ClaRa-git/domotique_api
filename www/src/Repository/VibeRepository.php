<?php

namespace App\Repository;

use App\Entity\Vibe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vibe>
 */
class VibeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vibe::class);
    }

    //    /**
    //     * @return Vibe[] Returns an array of Vibe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Vibe
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Méthode pour récupérer les vibes d'un profil
     * @param int $profileId
     * @return Vibe[]
     */
    public function getAllForUser(int $profileId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'v',
        ])
            ->from(Vibe::class, 'v')
            ->join('v.profile', 'p')
            ->where('p.id = :profileId')
            ->setParameter('profileId', $profileId)
            ->getQuery();

        $result = $query->getResult();

        return $result;       
    }
}
