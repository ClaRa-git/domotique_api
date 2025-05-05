<?php

namespace App\Scheduler;

use App\Message\LogHello;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule]
class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache
    )
    {
    }

    public function getSchedule(): Schedule
    {
        $schedule = new Schedule();

        // Exemple d'appel à ta méthode personnalisée
        $schedule->add(
            $this->createRecurringLogHello(
                interval: '1 day', 
                length: 6,
                startTime: '09:45',
                duration: 'PT1H'
            )
        );

        return $schedule;
    }

    private function createRecurringLogHello(
        string $interval, 
        int $length, 
        string $startTime, 
        string $duration = 'PT1H'
    ): RecurringMessage
    {
        $from = new \DateTimeImmutable($startTime, new \DateTimeZone('Europe/Paris'));
        $until = $from->add(new \DateInterval($duration));

        return RecurringMessage::every(
            frequency: $interval,
            message: new LogHello($length),
            from: $from,
            until: $until
        );
    }
}
