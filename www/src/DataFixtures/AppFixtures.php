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

    public function loadAvatars(ObjectManager $manager) {
        $array_avatars = [
            [
                'imagePath' => 'avatars/avatar1.png'
            ],
            [
                'imagePath' => 'avatars/avatar2.png'
            ],
            [
                'imagePath' => 'avatars/avatar3.png'
            ],
            [
                'imagePath' => 'avatars/avatar4.png'
            ],
            [
                'imagePath' => 'avatars/avatar5.png'
            ],
            [
                'imagePath' => 'avatars/avatar6.png'
            ],
            [
                'imagePath' => 'avatars/avatar7.png'
            ],
            [
                'imagePath' => 'avatars/avatar8.png'
            ],
            [
                'imagePath' => 'avatars/avatar9.png'
            ],
            [
                'imagePath' => 'avatars/avatar10.png'
            ],
            [
                'imagePath' => 'avatars/avatar11.png'
            ],
            [
                'imagePath' => 'avatars/avatar12.png'
            ],
            [
                'imagePath' => 'avatars/avatar13.png'
            ]
        ];
        foreach ($array_avatars as $key => $avatar) {
            $new_avatar = new Avatar();
            $new_avatar->setImagePath($avatar['imagePath']);

            $manager->persist($new_avatar);

            $this->addReference('avatar_' . $key + 1, $new_avatar);
        }
    }

    public function loadProfiles(ObjectManager $manager): void
    {
        $array_profiles = [
            [
                'username' => 'user1',
                'password' => 'user1',
                'avatar' => 3
            ]
        ];
        foreach ($array_profiles as $profile) {
            $new_profile = new Profile();
            $new_profile->setUsername($profile['username']);
            
            // Comme on utilise une classe profile et pas user, on ne peut pas utiliser le passwordHasher
            // on va donc le hasher manuellement sans utiliser le passwordHasher
            $hashedPassword = password_hash($profile['password'], PASSWORD_BCRYPT);
            $new_profile->setPassword($hashedPassword);
            
            $new_profile->setAvatar($this->getReference('avatar_' . $profile['avatar'], Avatar::class));

            $manager->persist($new_profile);
        }            
    }
}
