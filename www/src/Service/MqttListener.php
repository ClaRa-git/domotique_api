<?php 

namespace App\Service;

use App\Entity\DefaultSetting;
use PhpMqtt\Client\MqttClient as PhpMqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Protocole;

class MqttListener
{
    private PhpMqttClient $mqtt;
    private EntityManagerInterface $em;
    private DeviceRepository $deviceRepo;

    public function __construct(
        string $host,
        int $port,
        string $username,
        string $password,
        EntityManagerInterface $em,
        DeviceRepository $deviceRepo
    ) {
        $clientId = 'symfony_listener_' . uniqid();
        $this->mqtt = new PhpMqttClient($host, $port, $clientId);
        $settings = new ConnectionSettings();

        if (!empty(trim($username))) {
            $settings->setUsername($username);
            $settings->setPassword($password);
        }

        $this->mqtt->connect($settings, true);
        $this->em = $em;
        $this->deviceRepo = $deviceRepo;
    }

    public function listen(): void
    {
        $this->mqtt->subscribe('devices/+', function (string $topic, string $message) {
            $this->handleMessage($topic, $message);
        }, 0);

        $this->mqtt->loop(true); // true = indéfini
    }

    private function handleMessage(string $topic, string $message): void
    {
        $data = json_decode($message, true);

        if (!$data) {
            echo "Message JSON invalide : $message\n";
            return;
        }
        
        if (!isset($data['protocole'], $data['label'], $data['address'], $data['settings'])) {
            echo "Champs manquants dans le message reçu : " . json_encode($data) . "\n";
            return;
        }
        
        $existingDevice = $this->deviceRepo->findOneBy(['address' => $data['address']]);
        if ($existingDevice) {
            echo "Appareil déjà existant à l'adresse : " . $data['address'] . "\n";
            return;
        }

        $device = new Device();
        $device->setLabel($data['label']);
        $device->setAddress($data['address']);
        $device->setBrand($data['brand'] ?? null);
        $device->setReference($data['reference'] ?? null);

        // Vérifie si le DeviceType existe déjà
        $deviceType = $this->em->getRepository(DeviceType::class)->findOneBy(['label' => $data['deviceType']]);
        if ($deviceType) {
            $device->setDeviceType($deviceType);        
        } else {
            $deviceType = new DeviceType();
            $deviceType->setLabel($data['deviceType']);

            // Vérifie si le Protocole existe déjà
            $protocole = $this->em->getRepository(Protocole::class)->findOneBy(['label' => $data['protocole']]);
            if ($protocole) {
                $deviceType->setProtocole($protocole);
            } else {
                $protocole = new Protocole();
                $protocole->setLabel($data['protocole']);
                $this->em->persist($protocole);
                $deviceType->setProtocole($protocole);
            }

            $this->em->persist($deviceType);
            $device->setDeviceType($deviceType);
        }

        $this->em->persist($device);

        foreach ($data['settings'] as $setting) {
            $newSetting = new DefaultSetting();
            $newSetting->setValue($setting['value']);
            $newSetting->setDevice($device);

            // On vérifie si la feature existe déjà grâce à son label et au DeviceType
            $feature = $this->em->getRepository(Feature::class)->findOneBy([
                'label' => $setting['feature'],
                'deviceType' => $deviceType
            ]);

            if ($feature) {
                $newSetting->setFeature($feature);
            } else {
                $feature = new Feature();
                $feature->setLabel($setting['feature']);
                $this->em->persist($feature);
                $newSetting->setFeature($feature);
                $feature->setDeviceType($deviceType);
                $deviceType->addFeature($feature);
            }

            $this->em->persist($newSetting);
        }

        $this->em->flush();

    }
}
