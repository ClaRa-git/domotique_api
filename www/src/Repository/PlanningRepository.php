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

    public function getPlanningForDate(\DateTimeInterface $date): array
    {
        // On créé une date de début à 23:59:59
        $formattedDateStart = new \DateTime($date->format('Y-m-d H:i:s'));
        $formattedDateStart->setTime(23, 59, 59);

        // On crée une date de fin à 00:00:00
        $formattedDateEnd = new \DateTime($date->format('Y-m-d H:i:s'));
        $formattedDateEnd->setTime(0, 0, 0);

        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select('p')
            ->from(Planning::class, 'p')
            ->where('p.dateStart <= :dateStart')
            ->andWhere('p.dateEnd >= :dateEnd')
            ->setParameter('dateStart', $formattedDateStart->format('Y-m-d H:i:s'))
            ->setParameter('dateEnd', $formattedDateEnd->format('Y-m-d H:i:s'))
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }
}
