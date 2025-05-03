<?php

namespace App\Controller;

use App\Entity\DefaultSetting;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Protocole;
use App\Entity\Setting;
use App\Entity\Vibe;
use App\Repository\DeviceRepository;
use App\Repository\FeatureRepository;
use App\Repository\PlanningRepository;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Definition;
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
    #[Route('/api/device/init', name: 'api_device_init', methods: ['POST'])]
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
}
