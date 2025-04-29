<?php

namespace App\Controller;

use App\Entity\DefaultSetting;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Protocole;
use App\Entity\Setting;
use App\Repository\DeviceRepository;
use App\Repository\FeatureRepository;
use App\Repository\PlanningRepository;
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
        $device->setState(0);

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
    public function getPlanningForDate(Request $request, EntityManagerInterface $em, PlanningRepository $planningRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['date'])) {
            return new JsonResponse(['error' => 'Invalid payload'], 400);
        }

        $date = new \DateTime($data['date']);

        $plannings = $planningRepository->getPlanningForDate($date);

        return $this->json([
            'status' => 'ok',
            'plannings' => $plannings,
        ]);        
    }
}
