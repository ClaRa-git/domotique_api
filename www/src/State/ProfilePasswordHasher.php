<?php
namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Profile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfilePasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Profile && $data->getPassword()) {
            $plain = $data->getPassword();
            if (!str_starts_with($plain, '$2y$') && !str_starts_with($plain, '$argon')) {
                $data->setPassword(
                    $this->passwordHasher->hashPassword($data, $plain)
                );
            }
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}