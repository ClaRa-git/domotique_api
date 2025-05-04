<?php

namespace App\Command;

use App\Service\MqttListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MqttListenCommand extends Command
{
    protected static $defaultName = 'app:mqtt:listen';
    private MqttListener $listener;

    public function __construct(MqttListener $listener)
    {
        parent::__construct();
        $this->listener = $listener;
    }

    protected function configure()
    {
        $this->setDescription('Écoute les messages MQTT et enregistre les devices');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Démarrage de l’écoute MQTT...');
        $this->listener->listen();
        return Command::SUCCESS;
    }
}
