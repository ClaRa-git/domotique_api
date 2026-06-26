<?php

namespace App\MessageHandler;

use App\Entity\VibePlaying;
use App\Message\CheckPlannings;
use App\Repository\PlanningRepository;
use App\Service\MqttClient;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CheckPlanningsHandler
{
    private const PARIS = 'Europe/Paris';

    private const FRENCH_DAYS = [
        0 => 'dimanche',
        1 => 'lundi',
        2 => 'mardi',
        3 => 'mercredi',
        4 => 'jeudi',
        5 => 'vendredi',
        6 => 'samedi',
    ];

    public function __construct(
        private PlanningRepository $planningRepository,
        private MqttClient $mqttClient,
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CheckPlannings $message): void
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone(self::PARIS));
        $time = $now->format('H:i');
        $day = self::FRENCH_DAYS[(int)$now->format('w')];

        $this->logger->info('Tick scheduler', ['time' => $time, 'day' => $day, 'date' => $now->format('Y-m-d')]);

        $this->startPlannings($time, $now, $day);
        $this->stopPlannings($time, $now, $day);
    }

    private function startPlannings(string $time, \DateTimeImmutable $now, string $day): void
    {
        $plannings = $this->planningRepository->findActiveForCurrentMinute($time, $now, $day);
        $this->logger->info('Plannings à démarrer', ['count' => count($plannings)]);

        foreach ($plannings as $planning) {
            $vibe = $planning->getVibe();
            if (!$vibe) {
                continue;
            }

            // Crée un VibePlaying et le lie aux rooms du planning
            $vibePlaying = new VibePlaying();
            $vibePlaying->setVibe($vibe);
            $vibePlaying->setProfile($planning->getProfile());
            $this->em->persist($vibePlaying);
            $this->em->flush();

            foreach ($planning->getRooms() as $room) {
                $room->setVibePlaying($vibePlaying);
                $this->em->persist($room);

                // Publie les settings de la vibe pour chaque device de la room
                foreach ($room->getDevices() as $device) {
                    foreach ($vibe->getSettings() as $setting) {
                        if ($setting->getDevice()?->getId() !== $device->getId()) {
                            continue;
                        }

                        $topic = 'device/' . $device->getAddress();

                        if ($setting->getFeature()?->getLabel() === 'Play') {
                            $songs = $vibe->getPlaylist()?->getSongs() ?? [];
                            $payload = json_encode([
                                'playlist' => $songs,
                                'ref'      => $device->getReference(),
                                'label'    => $device->getLabel(),
                                'feature'  => 'Play',
                                'address'  => $device->getAddress(),
                            ]);
                        } else {
                            $payload = json_encode([
                                'value'   => $setting->getValue(),
                                'ref'     => $device->getReference(),
                                'label'   => $device->getLabel(),
                                'feature' => $setting->getFeature()?->getLabel(),
                                'address' => $device->getAddress(),
                            ]);
                        }

                        $this->mqttClient->publish($topic, $payload);
                    }
                }
            }

            $this->em->flush();

            $this->logger->info('Planning démarré', [
                'planning' => $planning->getLabel(),
                'vibe'     => $vibe->getLabel(),
                'time'     => $time,
            ]);
        }
    }

    private function stopPlannings(string $time, \DateTimeImmutable $now, string $day): void
    {
        $plannings = $this->planningRepository->findEndingAtCurrentMinute($time, $now, $day);

        foreach ($plannings as $planning) {
            $vibe = $planning->getVibe();
            if (!$vibe) {
                continue;
            }

            foreach ($planning->getRooms() as $room) {
                // Supprime le VibePlaying lié à la room
                $vibePlaying = $room->getVibePlaying();
                if ($vibePlaying) {
                    $room->setVibePlaying(null);
                    $this->em->persist($room);
                    $this->em->remove($vibePlaying);
                }

                // Envoie le stop à chaque device de la room
                foreach ($room->getDevices() as $device) {
                    $this->mqttClient->publish(
                        'device/' . $device->getAddress(),
                        json_encode([
                            'stop'        => true,
                            'deviceLabel' => $device->getLabel(),
                            'feature'     => 'On/Off',
                            'value'       => 'false',
                            'address'     => $device->getAddress(),
                        ]),
                    );
                }
            }

            $this->em->flush();

            $this->logger->info('Planning arrêté', [
                'planning' => $planning->getLabel(),
                'vibe'     => $vibe->getLabel(),
                'time'     => $time,
            ]);
        }
    }
}
