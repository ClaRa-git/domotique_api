<?php

namespace App\Repository;

use App\Entity\Planning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Planning>
 */
class PlanningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Planning::class);
    }

    //    /**
    //     * @return Planning[] Returns an array of Planning objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Planning
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Méthode pour récupérer les plannings d'une date donnée
     * @param \DateTimeInterface $date
     * @return array
     */
    public function getPlanningForDate(\DateTimeInterface $date): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('p')
            ->from(Planning::class, 'p')
            ->where('p.createdAt = :date')
            ->andWhere('p.recurrence = :none')
            ->setParameter('none', 'none')
            ->setParameter('date', $date)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode pour récupérer les plannings récurrents d'un jour donné
     * @param string $day
     * @return array
     */
    public function getWeeklyPlanning(string $day): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('p')
            ->from(Planning::class, 'p')
            ->where('p.recurrence <> :none')
            ->andWhere('p.recurrence <> :daily')
            ->andWhere('p.dayCreation = :day')
            ->setParameter('day', $day)
            ->setParameter('none', 'none')
            ->setParameter('daily', 'daily')
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }

    /**
     * Méthode pour récupérer les plannings quotidiens
     * @return array
     */
    public function getDailyPlannings(\DateTimeInterface $date): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('p')
            ->from(Planning::class, 'p')
            ->where('p.recurrence = :daily')
            ->andWhere('p.createdAt <= :date')
            ->setParameter('daily', 'daily')
            ->setParameter('date', $date)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }
}
