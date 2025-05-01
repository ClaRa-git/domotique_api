<?php

namespace App\Repository;

use App\Entity\DefaultSetting;
use App\Entity\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Setting>
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

//    /**
//     * @return Setting[] Returns an array of Setting objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Setting
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * Méthode pour récupérer les réglages d'un appareil pour une ambiance donnée
     * @param int $deviceId
     * @param int $vibeId
     * @return array
     */
    public function getSettingsDeviceVibe($deviceId, $vibeId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            's',
            'f',
            'u'
            ])
            ->from(Setting::class, 's')
            ->join('s.feature', 'f')
            ->leftJoin('f.unit', 'u')
            ->where('s.device = :deviceId')
            ->andWhere('s.vibe = :vibeId')
            ->setParameter('deviceId', $deviceId)
            ->setParameter('vibeId', $vibeId)
            ->getQuery();
        
        $results = $query->getResult();

        return $results;
    }

    /**
     * Méthode pour récupérer les réglages par défaut d'un appareil
     * @param int $deviceId
     * @return array
     */
    public function getDefaultSettings($deviceId): array
    {
        $entityManager = $this->getEntityManager();

        $qb = $entityManager->createQueryBuilder();

        $query = $qb->select([
            'ds',
            'f',
            'u'
            ])
            ->from(DefaultSetting::class, 'ds')
            ->join('ds.feature', 'f')
            ->leftJoin('f.unit', 'u')
            ->where('ds.device = :deviceId')
            ->setParameter('deviceId', $deviceId)
            ->getQuery();
        
        $results = $query->getResult();

        return $results;
    }
}
