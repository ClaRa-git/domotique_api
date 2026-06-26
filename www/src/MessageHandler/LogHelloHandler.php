<?php

namespace App\MessageHandler;

use App\Message\LogHello;
use App\Service\MqttClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class LogHelloHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private MqttClient $mqttClient,
    ) {
    }

    public function __invoke(LogHello $message): void
    {
        $this->logger->info('Hello from LogHelloHandler!');
        // Logique de traitement du message
        // Publier un message MQTT pour lancer la vibe
        $this->mqttClient->publish('topic', 'message');
    }
}
