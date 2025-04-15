<?php

namespace App\DataFixtures;

use App\Entity\Avatar;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Icon;
use App\Entity\Profile;
use App\Entity\Room;
use App\Entity\Setting;
use App\Entity\Unit;
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

        $this->loadIcons($manager);

        $this->loadAvatars($manager);

        $this->loadProfiles($manager);

        $this->loadUnit($manager);

        $this->loadDeviceType($manager);

        $this->loadFeature($manager);

        $this->loadRoom($manager);

        $this->loadDevice($manager);

        $this->loadSetting($manager);

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

    public function loadIcons(ObjectManager $manager): void
    {
        $array_icons = [
            [
                'path' =>'chill.png'
            ],
            [
                'path' =>'couple.png'
            ],
            [
                'path' =>'cozy.png'
            ],
            [
                'path' =>'friend.png'
            ],
            [
                'path' =>'party.png'
            ]
        ];

        foreach ($array_icons as $key => $icon) {
            $new_icon = new Icon();
            $new_icon->setImagePath($icon['path']);

            $manager->persist($new_icon);
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

    public function loadUnit(ObjectManager $manager): void
    {
        $array_units = [
            [
                'label' => 'Celsius',
                'symbol' => '°C',
            ],
            [
                'label' => 'Watt',
                'symbol' => 'W',
            ],
            [
                'label' => 'Lumen',
                'symbol' => 'lm',
            ]
        ];

        foreach ($array_units as $key => $unit) {
            $new_unit = new Unit();
            $new_unit->setLabel($unit['label']);
            $new_unit->setSymbol($unit['symbol']);

            $manager->persist($new_unit);

            // Création des références
            $this->addReference('unit_' . $key + 1, $new_unit);
        }
    }

    public function loadDeviceType(ObjectManager $manager): void
    {
        $array_device_types = [
            [
                'label' => 'Thermostat'
            ],
            [
                'label' => 'Ampoule'
            ],
            [
                'label' => 'Prise'
            ],
            [
                'label' => 'Capteur'
            ]
        ];

        foreach ($array_device_types as $key => $device_type) {
            $new_device_type = new DeviceType();
            $new_device_type->setLabel($device_type['label']);

            $manager->persist($new_device_type);

            // Création des références
            $this->addReference('device_type_' . $key + 1, $new_device_type);
        }
    }

    public function loadFeature(ObjectManager $manager): void
    {
        $array_features = [
            [
                'label' => 'Température',
                'unit' => 1
            ],
            [
                'label' => 'Luminosité',
                'unit' => 2
            ]
        ];

        foreach ($array_features as $key => $feature) {
            $new_feature = new Feature();
            $new_feature->setLabel($feature['label']);
            $new_feature->setUnit($this->getReference('unit_' . $feature['unit'], Unit::class));

            $manager->persist($new_feature);

            // Création des références
            $this->addReference('feature_' . $key + 1, $new_feature);
        }
    }

    public function loadRoom(ObjectManager $manager): void
    {
        $array_rooms = [
            [
                'label' => 'Salon',
                'image_path' => 'salon.jpg'
            ],
            [
                'label' => 'Chambre',
                'image_path' => 'chambre.jpg'
            ]
        ];

        foreach ($array_rooms as $key => $room) {
            $new_room = new Room();
            $new_room->setLabel($room['label']);
            $new_room->setImagePath($room['image_path']);

            $manager->persist($new_room);

            // Création des références
            $this->addReference('room_' . $key + 1, $new_room);
        }
    }

    public function loadDevice(ObjectManager $manager): void
    {
        $array_devices = [
            [
                'label' => 'Ampoule Salon',
                'address' => '1234567',
                'brand' => 'Philips',
                'reference' => '1234567',
                'state' => 0,
                'device_type' => 2,
                'room' => 1
            ],
            [
                'label' => 'Ampoule chambre',
                'address' => '1234568',
                'brand' => 'Philips',
                'reference' => '1234568',
                'state' => 0,
                'device_type' => 2,
                'room' => 2
            ]
        ];

        foreach ($array_devices as $key => $device) {
            $new_device = new Device();
            $new_device->setLabel($device['label']);
            $new_device->setDeviceType($this->getReference('device_type_' . $device['device_type'], DeviceType::class));
            $new_device->setRoom($this->getReference('room_' . $device['room'], Room::class));
            $new_device->setAddress($device['address']);
            $new_device->setBrand($device['brand']);
            $new_device->setReference($device['reference']);
            $new_device->setState($device['state']);

            $manager->persist($new_device);

            // Création des références
            $this->addReference('device_' . $key + 1, $new_device);
        }
    }

    public function loadSetting(ObjectManager $manager): void
    {
        $array_settings = [
            [
                'value' => '60',
                'vibe' => null,
                'device' => 1,
                'feature' => 2
            ],
            [
                'value' => '20',
                'vibe' => null,
                'device' => 2,
                'feature' => 2
            ]
        ];

        foreach ($array_settings as $key => $setting) {
            $new_setting = new Setting();
            $new_setting->setValue($setting['value']);
            $new_setting->setDevice($this->getReference('device_' . $setting['device'], Device::class));
            $new_setting->setFeature($this->getReference('feature_' . $setting['feature'], Feature::class));

            $manager->persist($new_setting);
        }
    }
}
