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
     * Plannings dont l'heure de déclenchement correspond à $time (format "HH:MM"),
     * qu'ils soient ponctuels, quotidiens ou hebdomadaires.
     * @return Planning[]
     */
    public function findActiveForCurrentMinute(string $time, \DateTimeInterface $date, string $dayLabel): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from(Planning::class, 'p')
            ->where(
                $qb->expr()->orX(
                    // Ponctuel : jour exact
                    $qb->expr()->andX(
                        'p.recurrence = :none',
                        'p.dateStart = :date',
                    ),
                    // Quotidien : démarré depuis dateStart
                    $qb->expr()->andX(
                        'p.recurrence = :daily',
                        'p.dateStart <= :date',
                    ),
                    // Hebdomadaire : même jour de création
                    $qb->expr()->andX(
                        'p.recurrence <> :none',
                        'p.recurrence <> :daily',
                        'p.dayCreation = :day',
                    ),
                )
            )
            ->andWhere('p.hourStart = :time')
            ->setParameter('none', 'none')
            ->setParameter('daily', 'daily')
            ->setParameter('date', \DateTime::createFromInterface($date), \Doctrine\DBAL\Types\Types::DATE_MUTABLE)
            ->setParameter('day', $dayLabel)
            ->setParameter('time', $time)
            ->getQuery()
            ->getResult();
    }

    /**
     * Plannings dont l'heure de fin correspond à $time — même logique que findActiveForCurrentMinute.
     * @return Planning[]
     */
    public function findEndingAtCurrentMinute(string $time, \DateTimeInterface $date, string $dayLabel): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from(Planning::class, 'p')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->andX('p.recurrence = :none', 'p.dateStart = :date'),
                    $qb->expr()->andX('p.recurrence = :daily', 'p.dateStart <= :date'),
                    $qb->expr()->andX(
                        'p.recurrence <> :none',
                        'p.recurrence <> :daily',
                        'p.dayCreation = :day',
                    ),
                )
            )
            ->andWhere('p.hourEnd = :time')
            ->setParameter('none', 'none')
            ->setParameter('daily', 'daily')
            ->setParameter('date', \DateTime::createFromInterface($date), \Doctrine\DBAL\Types\Types::DATE_MUTABLE)
            ->setParameter('day', $dayLabel)
            ->setParameter('time', $time)
            ->getQuery()
            ->getResult();
    }

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
            ->where('p.dateStart = :date')
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
            ->andWhere('p.dateStart <= :date')
            ->setParameter('daily', 'daily')
            ->setParameter('date', $date)
            ->getQuery();
        
        $result = $query->getResult();

        return $result;
    }
}
