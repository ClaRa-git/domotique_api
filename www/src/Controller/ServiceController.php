<?php

namespace App\Controller;

use App\Entity\Criteria;
use App\Entity\DefaultSetting;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Icon;
use App\Entity\Playlist;
use App\Entity\Profile;
use App\Entity\Protocole;
use App\Entity\Room;
use App\Entity\Setting;
use App\Entity\Vibe;
use App\Entity\VibePlaying;
use App\Repository\DeviceRepository;
use App\Repository\FeatureRepository;
use App\Repository\PlanningRepository;
use App\Repository\SettingRepository;
use App\Service\MqttClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    // Méthode utilitaire — vérifie que le userId du body == user JWT connecté
    private function assertOwnership(int $requestedUserId): void
    {
        $user = $this->getUser();
        if (!$user || $user->getId() !== $requestedUserId) {
            throw $this->createAccessDeniedException('Accès interdit à cette ressource.');
        }
    }

    // Vérifie que la vibe appartient au user connecté
    private function assertVibeOwnership(Vibe $vibe): void
    {
        $user = $this->getUser();
        if (!$user || $vibe->getProfile()->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException('Accès interdit à cette ressource.');
        }
    }

    #[Route('/service-device', name: 'app_service_device')]
    public function getDeviceForVibe(Request $request, DeviceRepository $deviceRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $results = $deviceRepository->getDeviceSettingForRoomAndVibe($data['roomId'], $data['vibeId']);

        return $this->json($results);
    }

    #[Route('/api/device-init', name: 'api_device_init', methods: ['POST'])]
    public function initDevice(Request $request, EntityManagerInterface $em, FeatureRepository $featureRepo): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['label'], $data['address'], $data['settings'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $device = new Device();
        $device->setLabel($data['label']);
        $device->setAddress($data['address']);
        $device->setBrand($data['brand'] ?? null);
        $device->setReference($data['reference'] ?? null);

        $deviceType = $em->getRepository(DeviceType::class)->findOneBy(['label' => $data['deviceType']]);
        if ($deviceType) {
            $device->setDeviceType($deviceType);        
        } else {
            $deviceType = new DeviceType();
            $deviceType->setLabel($data['deviceType']);

            $protocole = $em->getRepository(Protocole::class)->findOneBy(['label' => $data['protocole']]);
            if ($protocole) {
                $deviceType->setProtocole($protocole);
            } else {
                $protocole = new Protocole();
                $protocole->setLabel($data['protocole']);
                $em->persist($protocole);
                $deviceType->setProtocole($protocole);
            }

            $em->persist($deviceType);
            $device->setDeviceType($deviceType);
        }

        $em->persist($device);

        foreach ($data['settings'] as $setting) {
            $newSetting = new DefaultSetting();
            $newSetting->setValue($setting['value']);
            $newSetting->setDevice($device);

            $feature = $em->getRepository(Feature::class)->findOneBy(['label' => $setting['feature']]);
            if ($feature) {
                $newSetting->setFeature($feature);
            } else {
                $feature = new Feature();
                $feature->setLabel($setting['feature']);
                $em->persist($feature);
                $newSetting->setFeature($feature);
                $feature->setDeviceType($deviceType);
                $deviceType->addFeature($feature);
            }

            $em->persist($newSetting);
        }

        $em->flush();

        return new JsonResponse(['status' => 'ok', 'device_id' => $device->getId()]);
    }

    #[Route('/service-planning', name: 'app_service_planning', methods: ['POST'])]
    public function getPlanningsForDate(Request $request, EntityManagerInterface $em, PlanningRepository $planningRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['date'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $date = new \DateTime($data['date']);
        $day = $data['day'];

        $planningsForDay = $planningRepository->getPlanningForDate($date);
        $weeklyPlannings = $planningRepository->getWeeklyPlanning($day);
        $dailyPlannings = $planningRepository->getDailyPlannings($date);

        $plannings = array_merge($planningsForDay, $weeklyPlannings, $dailyPlannings);

        return $this->json([
            'status' => 'ok',
            'plannings' => $plannings,
        ]);        
    }

    #[Route('/service-setting', name: 'app_service_setting', methods: ['POST'])]
    public function getSettingsForDevice(Request $request, SettingRepository $settingRepository, FeatureRepository $featureRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['deviceId'], $data['vibeId'], $data['deviceTypeId'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $deviceId = $data['deviceId'];
        $vibeId = $data['vibeId'];

        $settings = $settingRepository->getSettingsDeviceVibe($deviceId, $vibeId);

        $formattedSettings = [];

        foreach ($settings as $setting) {
            $formattedSettings[] = [
                'id' => $setting->getId(),
                'featureId' => $setting->getFeature()->getId(),
                'featureLabel' => $setting->getFeature()->getLabel(),
                'deviceAddress' => $setting->getDevice()->getAddress(),
                'deviceRef' => $setting->getDevice()->getReference(),
                'deviceLabel' => $setting->getDevice()->getLabel(),
                'deviceId' => $setting->getDevice()->getId(),
                'vibeId' => $setting->getVibe()->getId(),
                'label' => $setting->getFeature()->getLabel(),
                'value' => $setting->getValue(),
                'unit' => $setting->getFeature()->getUnit() ? $setting->getFeature()->getUnit()->getSymbol() : null,
                'minimum' => $setting->getFeature()->getMinimum(),
                'maximum' => $setting->getFeature()->getMaximum(),
            ];
        }

        $defaultSettings = $settingRepository->getDefaultSettings($deviceId);

        if (count($settings) !== count($defaultSettings)) {
            foreach ($defaultSettings as $setting) {
                $found = false;
                foreach ($settings as $s) {
                    if ($s->getFeature()->getId() === $setting->getFeature()->getId()) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $formattedSettings[] = [
                        'id' => 0,
                        'featureId' => $setting->getFeature()->getId(),
                        'featureLabel' => $setting->getFeature()->getLabel(),
                        'deviceAddress' => $setting->getDevice()->getAddress(),
                        'deviceRef' => $setting->getDevice()->getReference(),
                        'deviceLabel' => $setting->getDevice()->getLabel(),
                        'deviceId' => $setting->getDevice()->getId(),
                        'vibeId' => (int)$vibeId,
                        'label' => $setting->getFeature()->getLabel(),
                        'value' => $setting->getValue(),
                        'unit' => $setting->getFeature()->getUnit() ? $setting->getFeature()->getUnit()->getSymbol() : null,
                        'minimum' => $setting->getFeature()->getMinimum(),
                        'maximum' => $setting->getFeature()->getMaximum(),
                    ];
                }
            }

            return $this->json([
                'status' => 'ok',
                'message' => 'Settings/Default settings found',
                'settings' => $formattedSettings
            ]);
        } 
        else if (empty($formattedSettings)) {

            foreach ($defaultSettings as $setting) {
                $formattedSettings[] = [
                    'id' => 0,
                    'featureId' => $setting->getFeature()->getId(),
                    'featureLabel' => $setting->getFeature()->getLabel(),
                    'deviceAddress' => $setting->getDevice()->getAddress(),
                    'deviceRef' => $setting->getDevice()->getReference(),
                    'deviceLabel' => $setting->getDevice()->getLabel(),
                    'deviceId' => $setting->getDevice()->getId(),
                    'vibeId' => (int)$vibeId,
                    'label' => $setting->getFeature()->getLabel(),
                    'value' => $setting->getValue(),
                    'unit' => $setting->getFeature()->getUnit() ? $setting->getFeature()->getUnit()->getSymbol() : null,
                    'minimum' => $setting->getFeature()->getMinimum(),
                    'maximum' => $setting->getFeature()->getMaximum(),
                ];
            }

            if (empty($formattedSettings)) {
                return $this->json([
                    'status' => 'ok',
                    'message' => 'No settings/defaut settings found',
                    'settings' => $formattedSettings
                ]);
            } else {
                return $this->json([
                    'status' => 'ok',
                    'message' => 'Default settings found',
                    'settings' => $formattedSettings
                ]);
            }
        } else {
            return $this->json([
                'status' => 'ok',
                'message' => 'Settings found',
                'settings' => $formattedSettings
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'No setting/feature found'
        ]);
    }

    #[Route('/service-settings-update', name: 'app_service-settings_update', methods: ['POST'])]
    public function updateSettings(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        foreach ($data as $settingData) {
            $setting = $em->getRepository(Setting::class)->find($settingData['id']);
            if ($setting) {
                // Vérifie que la vibe du setting appartient au user connecté
                $this->assertVibeOwnership($setting->getVibe());

                $setting->setValue($settingData['value']);
                $em->persist($setting);
            } 
            else {
                $device = $em->getRepository(Device::class)->find($settingData['deviceId']);
                $vibe = $em->getRepository(Vibe::class)->find($settingData['vibeId']);
                $feature = $em->getRepository(Feature::class)->find($settingData['featureId']);

                // Vérifie que la vibe appartient au user connecté
                $this->assertVibeOwnership($vibe);

                $setting = new Setting();
                $setting->setValue($settingData['value']);
                $setting->setDevice($device);
                $setting->setVibe($vibe);
                $setting->setFeature($feature);
                $em->persist($setting);
            }
        }
        
        $em->flush();

        return $this->json([
            'status' => 'ok',
            'message' => 'Settings updated'
        ]);
    }

    #[Route('/service-vibe-recommended', name: 'app_service_vibe_recommended', methods: ['POST'])]
    public function recommendVibes(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'];

        // Vérification IDOR
        $this->assertOwnership((int) $userId);

        $userMood = $data['mood'];
        $userTone = $data['tone'];
        $userStress = $data['stress'];

        $vibeRepository = $em->getRepository(Vibe::class);
        $vibes = $vibeRepository->getAllForUser($userId);

        $results = [];

        foreach ($vibes as $vibe) {
            $distance = sqrt(
                pow($vibe->getCriteria()->getMood() - $userMood, 2) +
                pow($vibe->getCriteria()->getTone() - $userTone, 2) +
                pow($vibe->getCriteria()->getStress() - $userStress, 2)
            );

            $results[] = ['vibe' => $vibe, 'distance' => $distance];
        }

        usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);

        $topVibes = array_slice(array_map(fn($r) => $r['vibe'], $results), 0, 2);

        $fullVibes = [];

        foreach ($topVibes as $vibe) {
            $criteriaId = $vibe->getCriteria()->getId();
            $criteria = $em->getRepository(Criteria::class)->find($criteriaId);
            
            $iconId = $vibe->getIcon()->getId();
            $icon = $em->getRepository(Icon::class)->find($iconId);

            $playlistId = $vibe->getPlaylist()->getId();
            $playlist = $em->getRepository(Playlist::class)->find($playlistId);

            $settings = $em->getRepository(Setting::class)->findBy(['vibe' => $vibe]);            
            $formattedSettings = [];

            foreach ($settings as $setting) {
                $device = $em->getRepository(Device::class)->find($setting->getDevice()->getId());

                $formattedSettings[] = [
                    'id' => $setting->getId(),
                    'value' => $setting->getValue(),
                    'deviceLabel' => $device->getLabel(),
                    'deviceRef' => $device->getReference(),
                    'deviceAddress' => $device->getAddress(),
                    'roomId' => $device->getRoom()->getId(),
                    'featureLabel' => $setting->getFeature()->getLabel()
                ];
            }

            $playlistSongs = [];
            $playlistId = 0;
            $playlistTitle = '';

            if ($playlist) {
                $playlistId = $playlist->getId();
                $playlistTitle = $playlist->getTitle();

                $songs = $playlist->getSongs();
                foreach ($songs as $song) {
                    $playlistSongs[] = [
                        'id' => $song->getId(),
                        'title' => $song->getTitle(),
                        'artist' => $song->getArtist(),
                        'duration' => $song->getDuration(),
                        'filePath' => $song->getFilePath(),
                        'imagePath' => $song->getImagePath()
                    ];
                }
            }

            $fullVibes[] = [
                'id' => $vibe->getId(),
                'label' => $vibe->getLabel(),
                'criteria' => [
                    'id' => $criteria->getId(),
                    'mood' => $criteria->getMood(),
                    'tone' => $criteria->getTone(),
                    'stress' => $criteria->getStress()
                ],
                'icon' => [
                    'id' => $icon->getId(),
                    'imagePath' => $icon->getImagePath()
                ],
                'playlist' => [
                    'id' => $playlistId,
                    'title' => $playlistTitle,
                    'songs' => $playlistSongs
                ],
                'settings' => $formattedSettings
            ];
        }

        return $this->json([
            'status' => 'ok',
            'vibes' => $fullVibes
        ]);
    }

    #[Route('/send-vibe', name: 'send_vibe', methods: ['POST'])]
    public function sendVibe(Request $request, EntityManagerInterface $em, MqttClient $mqttClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $settings = $data['settings'] ?? [];
        $vibeId = $data['vibeId'] ?? null;
        $roomId = $data['roomId'] ?? null;

        $vibe = $em->getRepository(Vibe::class)->find($vibeId);

        // Vérifie que la vibe appartient au user connecté
        $this->assertVibeOwnership($vibe);

        $newVibePlaying = new VibePlaying();
        $newVibePlaying->setVibe($vibe);
        $newVibePlaying->setProfile($em->getRepository(Profile::class)->find($vibe->getProfile()->getId()));
        $em->persist($newVibePlaying);
        $em->flush();

        $room = $em->getRepository(Room::class)->find($roomId);
        if ($room) {
            $room->setVibePlaying($newVibePlaying);
            $em->persist($room);
            $em->flush();
        }

        $playlist = $em->getRepository(Playlist::class)->find($vibe->getPlaylist()->getId());
        $songs = $playlist->getSongs();

        foreach ($settings as $setting) {
            $topic = 'device/' . $setting['deviceAddress'];

            if ($setting['featureLabel'] === 'Play') {
                $message = json_encode([
                    'playlist' => $songs,
                    'ref'   => $setting['deviceRef'],
                    'label' => $setting['deviceLabel'],
                    'feature' => $setting['featureLabel'],
                    'address' => $setting['deviceAddress']
                ]);
            } else {
                $message = json_encode([
                    'value' => $setting['value'],
                    'ref'   => $setting['deviceRef'],
                    'label' => $setting['deviceLabel'],
                    'feature' => $setting['featureLabel'],
                    'address' => $setting['deviceAddress']
                ]);
            }

            $mqttClient->publish($topic, $message);
        }

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/stop-vibe', name: 'stop_vibe', methods: ['POST'])]
    public function stopVibe(Request $request, MqttClient $mqttClient, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $vibePlayingId = $data['vibePlayingId'];
        $vibeId = $data['vibeId'];
        $roomId = $data['roomId'];

        // Vérifie que la vibe appartient au user connecté
        $vibe = $em->getRepository(Vibe::class)->find($vibeId);
        $this->assertVibeOwnership($vibe);

        $room = $em->getRepository(Room::class)->find($roomId);
        if ($room) {
            $room->setVibePlaying(null);
            $em->persist($room);
            $em->flush();
        }

        $vibePlaying = $em->getRepository(VibePlaying::class)->find($vibePlayingId);
        if ($vibePlaying) {
            $em->remove($vibePlaying);
            $em->flush();
        }

        $devices = $em->getRepository(Device::class)->findBy(['room' => $roomId]);

        foreach ($devices as $device) {
            $topic = 'device/' . $device->getAddress();

            $message = json_encode([
                'stop' => true,
                'deviceLabel' => $device->getLabel(),
                'feature' => "On/Off",
                'value' => "false",
                'address' => $device->getAddress()
            ]);

            $mqttClient->publish($topic, $message);
        }

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/stop-vibes-user', name: 'stop_vibes_user', methods: ['POST'])]
    public function stopVibesUser(Request $request, MqttClient $mqttClient, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['userId'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        // Vérification IDOR
        $this->assertOwnership((int) $data['userId']);

        $userId = $data['userId'];

        $vibes = $em->getRepository(Vibe::class)->findBy(['profile' => $userId]);

        $vibesPlaying = $em->getRepository(VibePlaying::class)->findBy(['profile' => $userId]);
        foreach ($vibesPlaying as $vibePlaying) {
            $em->remove($vibePlaying);
            $em->flush();
        }

        $rooms = $em->getRepository(Room::class)->findBy(['vibePlaying' => $vibesPlaying]);
        foreach ($rooms as $room) {
            $room->setVibePlaying(null);
            $em->persist($room);
            $em->flush();
        }

        $devices = $em->getRepository(Device::class)->findBy(['room' => $rooms]);

        foreach ($devices as $device) {
            $topic = 'device/' . $device->getAddress();

            $message = json_encode([
                'stop' => true,
                'deviceLabel' => $device->getLabel(),
                'feature' => "On/Off",
                'value' => "false",
                'address' => $device->getAddress()
            ]);

            $mqttClient->publish($topic, $message);
        }

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/test-settings', name: 'test-settings', methods: ['POST'])]
    public function testSettings(Request $request, EntityManagerInterface $em, MqttClient $mqttClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $settings = $data['settings'] ?? [];
        $vibeId = $data['vibeId'] ?? null;

        $vibe = $em->getRepository(Vibe::class)->find($vibeId);

        // Vérifie que la vibe appartient au user connecté
        $this->assertVibeOwnership($vibe);

        $playlist = $em->getRepository(Playlist::class)->find($vibe->getPlaylist()->getId());
        $songs = $playlist->getSongs();

        foreach ($settings as $setting) {
            $topic = 'device/' . $setting['deviceAddress'];

            if ($setting['featureLabel'] === 'Play') {
                $message = json_encode([
                    'playlist' => $songs,
                    'ref'   => $setting['deviceRef'],
                    'label' => $setting['deviceLabel'],
                    'feature' => $setting['featureLabel'],
                    'address' => $setting['deviceAddress']
                ]);
            } else {
                $message = json_encode([
                    'value' => $setting['value'],
                    'ref'   => $setting['deviceRef'],
                    'label' => $setting['deviceLabel'],
                    'feature' => $setting['featureLabel'],
                    'address' => $setting['deviceAddress']
                ]);
            }

            $mqttClient->publish($topic, $message);
        }

        return new JsonResponse(['status' => 'success']);
    }
}