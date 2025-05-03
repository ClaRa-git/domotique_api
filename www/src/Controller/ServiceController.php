<?php

namespace App\Controller;

use App\Entity\Criteria;
use App\Entity\DefaultSetting;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Icon;
use App\Entity\Playlist;
use App\Entity\Protocole;
use App\Entity\Setting;
use App\Entity\Vibe;
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
    /**
     * @Route("/service-device", name="app_service_device")
     * @param int $roomId
     * @param int $vibeId
     * @return Response
     */
    #[Route('/service-device', name: 'app_service_device')]
    public function getDeviceForVibe(Request $request, DeviceRepository $deviceRepository): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $results = $deviceRepository->getDeviceSettingForRoomAndVibe($data['roomId'], $data['vibeId']);

        return $this->json($results);
    }

    /**
     * @Route("/api/device/init", name="api_device_init")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param FeatureRepository $featureRepo
     * @return JsonResponse
     */
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

        // Vérifie si le DeviceType existe déjà
        $deviceType = $em->getRepository(DeviceType::class)->findOneBy(['label' => $data['deviceType']]);
        if ($deviceType) {
            $device->setDeviceType($deviceType);        
        } else {
            $deviceType = new DeviceType();
            $deviceType->setLabel($data['deviceType']);

            // Vérifie si le Protocole existe déjà
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

    /**
     * @Route("/service-planning", name="app_service_planning")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PlanningRepository $planningRepository
     * @return JsonResponse
     */
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

        // On combine les deux tableaux
        $plannings = array_merge($planningsForDay, $weeklyPlannings, $dailyPlannings);

        return $this->json([
            'status' => 'ok',
            'plannings' => $plannings,
        ]);        
    }

    /**
     * Récupère les settings s'ils existent d'un appareil pour une ambiance donnée
     * S'ils n'exitent pas, on récupère les réglages par défaut s'ils existent
     * S'ils n'existent pas, on récupère les features de l'appareil
     * S'ils n'existent pas, on renvoie un message d'erreur
     * @Route("/service-setting", name="app_service_setting")
     * @param Request $request
     * @param SettingRepository $settingRepository
     * @param FeatureRepository $featureRepository
     * @return JsonResponse
     */
    #[Route('/service-setting', name: 'app_service_setting', methods: ['POST'])]
    public function getSettingsForDevice(Request $request, SettingRepository $settingRepository, FeatureRepository $featureRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['deviceId'], $data['vibeId'], $data['deviceTypeId'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $deviceId = $data['deviceId'];
        $vibeId = $data['vibeId'];
        $deviceTypeId = $data['deviceTypeId'];

        // On essaie de récupère les réglages de l'appareil pour l'ambiance donnée 
        $settings = $settingRepository->getSettingsDeviceVibe($deviceId, $vibeId);

        // On créé un tableau parcourant $settings pour le formater
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

        // On regarde si l'appareil a des réglages par défaut
        $defaultSettings = $settingRepository->getDefaultSettings($deviceId);

        // Si le taille de settings est différente de celle de defaultSettings
        // On combine les deux tableaux
        if (count($settings) !== count($defaultSettings)) {
            // On garde les réglages de l'appareil et on rajoute les réglages par défaut qui n'exite pas dans $settings
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
        // S'il n'existe pas de réglages
        else if (empty($formattedSettings)) {

            // On créé un tableau parcourant $defaultSettings pour le formater
            foreach ($defaultSettings as $setting) {
                $formattedSettings[] = [
                    'id' => 0,
                    'featureId' => $setting->getFeature()->getId(),
                    'deviceId' => $setting->getDevice()->getId(),
                    'vibeId' => (int)$vibeId,
                    'label' => $setting->getFeature()->getLabel(),
                    'value' => $setting->getValue(),
                    'unit' => $setting->getFeature()->getUnit() ? $setting->getFeature()->getUnit()->getSymbol() : null,
                    'minimum' => $setting->getFeature()->getMinimum(),
                    'maximum' => $setting->getFeature()->getMaximum(),
                ];
            }

            // S'il n'existe pas de réglages par défaut
            if (empty($formattedSettings)) {

                // On recupère les features de l'appareil
                $features = $featureRepository->getFeaturesUnitsForType($deviceTypeId);
                // On créé un tableau parcourant $features pour le formater
                foreach ($features as $feature) {
                    $formattedSettings[] = [
                        'id' => 0,
                        'featureId' => $feature->getId(),
                        'deviceId' => (int)$deviceId,
                        'vibeId' => (int)$vibeId,
                        'label' => $feature->getLabel(),
                        'value' => $feature->getDefaultValue(),
                        'unit' => $feature->getUnit() ? $feature->getUnit()->getSymbol() : null,
                        'minimum' => $feature->getMinimum(),
                        'maximum' => $feature->getMaximum(),
                    ];
                }

                return $this->json([
                    'status' => 'ok',
                    'message' => 'Features found',
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

    /**
     * @Route("/settings-update", name="app_settings_update")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/service-settings-update', name: 'app_service-settings_update', methods: ['POST'])]
    public function updateSettings(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifie si le payload est vide
        if (!$data) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        foreach ($data as $settingData) {
            // Vérifie si le setting existe
            $setting = $em->getRepository(Setting::class)->find($settingData['id']);
            if ($setting) {
                // Met à jour la valeur du setting
                $setting->setValue($settingData['value']);
                $em->persist($setting);
            } 
            // Si le setting n'existe pas, on le crée
            else {
                $device = $em->getRepository(Device::class)->find($settingData['deviceId']);
                $vibe = $em->getRepository(Vibe::class)->find($settingData['vibeId']);
                $feature = $em->getRepository(Feature::class)->find($settingData['featureId']);

                $setting = new Setting();
                $setting->setValue($settingData['value']);
                $setting->setDevice($device);
                $setting->setVibe($vibe);
                $setting->setFeature($feature);
                $em->persist($setting);
            }

            // On met à jour le réglage
            $em->flush();
        }

        return $this->json([
            'status' => 'ok',
            'message' => 'Settings updated'
        ]);
    }

    /**
     * Méthode pour récupérer les vibes recommandées
     * @Route("/service-vibe-recommended", name="app_service_vibe_recommended")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/service-vibe-recommended', name: 'app_service_vibe_recommended', methods: ['POST'])]
    public function recommendVibes(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'];
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

        // retourne les 2 vibes les plus proches
        $topVibes = array_slice(array_map(fn($r) => $r['vibe'], $results), 0, 2);

        // On va réconstruire les vibes
        $fullVibes = [];

        // On récupère les critères des vibes
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

                // On récupère toutes les songs de la playlist
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

    /**
     * Méthode pour envoyer les réglages d'une ambiance à un appareil
     * @Route("/send-vibe", name="app_send_vibe")
     * @param Request $request
     * @param MqttService $mqttService
     * @return JsonResponse
     */
    #[Route('/send-vibe', name: 'send_vibe', methods: ['POST'])]
    public function sendVibe(Request $request, MqttClient $mqttClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $settings = $data['settings'] ?? [];

        foreach ($settings as $setting) {
            $topic = 'device/' . $setting['deviceAddress'];
            $message = json_encode([
                'value' => $setting['value'],
                'ref'   => $setting['deviceRef'],
                'label' => $setting['deviceLabel'],
                'feature' => $setting['featureLabel'],
                'address' => $setting['deviceAddress']
            ]);
            $mqttClient->publish($topic, $message);
        }

        return new JsonResponse(['status' => 'success']);
    }

}
