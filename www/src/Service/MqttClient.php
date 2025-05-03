<?php

namespace App\Service;

use PhpMqtt\Client\MqttClient as PhpMqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttClient
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;

    public function __construct(string $host, int $port, string $username = '', string $password = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function publish(string $topic, string $message): void
    {
        $mqtt = new PhpMqttClient($this->host, $this->port, uniqid());
        $settings = new ConnectionSettings();

        if (!empty(trim($this->username))) {
            $settings->setUsername($this->username);
            $settings->setPassword($this->password);
        }


        $mqtt->connect($settings, true);
        $mqtt->publish($topic, $message);
        $mqtt->disconnect();
    }
}
