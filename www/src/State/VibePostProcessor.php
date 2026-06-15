<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Setting;
use App\Entity\Vibe;
use App\Repository\DefaultSettingRepository;
use Doctrine\ORM\EntityManagerInterface;

class VibePostProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private DefaultSettingRepository $defaultSettingRepository,
        private EntityManagerInterface $em,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);

        if ($data instanceof Vibe) {
            foreach ($this->defaultSettingRepository->findAll() as $defaultSetting) {
                $setting = new Setting();
                $setting->setValue($defaultSetting->getValue());
                $setting->setFeature($defaultSetting->getFeature());
                $setting->setDevice($defaultSetting->getDevice());
                $setting->setVibe($data);
                $this->em->persist($setting);
            }
            $this->em->flush();
        }

        return $result;
    }
}
