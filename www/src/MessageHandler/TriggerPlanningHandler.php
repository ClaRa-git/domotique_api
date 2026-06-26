<?php

namespace App\MessageHandler;

use App\Message\TriggerPlanning;
use App\Repository\PlanningRepository;
use App\Service\MqttClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class TriggerPlanningHandler
{
    public function __construct(
        private PlanningRepository $planningRepository,
        private MqttClient $mqttClient,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(TriggerPlanning $message): void
    {
        $planning = $this->planningRepository->find($message->planningId);

        if (!$planning || !$planning->getVibe()) {
            $this->logger->warning('Planning introuvable ou sans vibe', ['id' => $message->planningId]);
            return;
        }

        $vibe = $planning->getVibe();

        foreach ($vibe->getSettings() as $setting) {
            $device = $setting->getDevice();
            if (!$device || !$device->getAddress()) {
                continue;
            }

            $topic = 'device/' . $device->getAddress();
            $payload = json_encode([
                'feature' => $setting->getFeature()?->getLabel(),
                'value'   => $setting->getValue(),
            ]);

            $this->mqttClient->publish($topic, $payload);
        }

        $this->logger->info('Planning déclenché', [
            'planning' => $planning->getLabel(),
            'vibe'     => $vibe->getLabel(),
        ]);
    }
}
