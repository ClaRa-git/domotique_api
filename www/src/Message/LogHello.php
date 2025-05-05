<?php

namespace App\Message;

use App\Service\MqttClient;
use Doctrine\ORM\EntityManagerInterface;

final class LogHello
{
    public function __construct()
    {
    }
}
