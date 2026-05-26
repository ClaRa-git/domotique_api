<?php

namespace App\Command;

use App\Repository\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:hash-existing-passwords',
    description: 'Migration one-shot : hache les mots de passe en clair en BDD',
)]
class HashExistingPasswordsCommand extends Command
{
    public function __construct(
        private ProfileRepository $profileRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $profiles = $this->profileRepository->findAll();
        $count = 0;

        foreach ($profiles as $profile) {
            $plainPassword = $profile->getPassword();

            // Si déjà haché (commence par $2y$ bcrypt ou $argon), on skip
            if (str_starts_with($plainPassword, '$2y$') || str_starts_with($plainPassword, '$argon')) {
                $io->note("Skipped (déjà haché) : {$profile->getUsername()}");
                continue;
            }

            $hashed = $this->passwordHasher->hashPassword($profile, $plainPassword);
            $profile->setPassword($hashed);
            $this->em->persist($profile);
            $count++;
            $io->writeln("✅ Haché : {$profile->getUsername()}");
        }

        $this->em->flush();
        $io->success("$count mot(s) de passe migré(s) avec succès.");

        return Command::SUCCESS;
    }
}