<?php

namespace App\DataFixtures;

use App\Entity\Avatar;
use App\Entity\Profile;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);

        $this->loadAvatars($manager);

        $this->loadProfiles($manager);

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager): void
    {
        $array_users = [
            [
                'username' => 'admin',
                'password' => 'admin',
                'roles' => ['ROLE_ADMIN']
            ]
        ];

        foreach ($array_users as $user) {
            $new_user = new User();
            $new_user->setUsername($user['username']);
            $new_user->setRoles($user['roles']);
            $new_user->setPassword($this->encoder->hashPassword($new_user, $user['password']));

            $manager->persist($new_user);
        }
    }

    public function loadAvatars(ObjectManager $manager): void
    {
        $array_avatars = [
            [
                'path' => 'avatar1.jpg'
            ],
            [
                'path' => 'avatar2.jpg'
            ],
            [
                'path' => 'avatar3.jpg'
            ],
            [
                'path' => 'avatar4.jpg'
            ],
            [
                'path' => 'avatar5.jpg'
            ],
            [
                'path' => 'avatar6.jpg'
            ],
            [
                'path' => 'avatar7.jpg'
            ],
            [
                'path' => 'avatar8.jpg'
            ],
            [
                'path' => 'avatar9.jpg'
            ],
            [
                'path' => 'avatar10.jpg'
            ],
            [
                'path' => 'avatar11.jpg'
            ],
            [
                'path' => 'avatar12.jpg'
            ],
            [
                'path' => 'avatar13.jpg'
            ]
            
        ];
        
        foreach ($array_avatars as $key => $avatar) {
            $new_avatar = new Avatar();
            $new_avatar->setImagePath($avatar['path']);

            // Création des références
            $this->addReference('avatar_' . $key + 1, $new_avatar);

            $manager->persist($new_avatar);
        }
    }

    public function loadProfiles(ObjectManager $manager): void
    {
        $array_profiles = [
            [
                'username' => 'user1',
                'password' => 'user1',
                'avatar' => 1
            ]
        ];
        foreach ($array_profiles as $profile) {
            $new_profile = new Profile();
            $new_profile->setUsername($profile['username']);
            
            // Comme on utilise une classe profile et pas user, on ne peut pas utiliser le passwordHasher
            // on va donc le hasher manuellement sans utiliser le passwordHasher
            // $hashedPassword = password_hash($profile['password'], PASSWORD_BCRYPT);
            // $new_profile->setPassword($hashedPassword);
            $new_profile->setPassword($profile['password']);

            // On récupère l'image à partir de la référence
            $new_profile->setAvatar($this->getReference('avatar_' . $profile['avatar'], Avatar::class));

            $manager->persist($new_profile);
        }            
    }
}
