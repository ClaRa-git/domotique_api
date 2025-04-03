<?php

namespace App\Controller\Admin;

use App\Entity\Avatar;
use App\Entity\Device;
use App\Entity\DeviceType;
use App\Entity\Feature;
use App\Entity\Image;
use App\Entity\Profile;
use App\Entity\Room;
use App\Entity\Setting;
use App\Entity\Song;
use App\Entity\Unit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(ProfileCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/images/logo.png" alt="Logo" style="height: 50px; margin-right: 10px;" >
                <span style="font-size: 18px; font-weight: bold;">Hoomy Admin</span>
            ')
            ->setFaviconPath('images/logo.png')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        // Menu principal
        yield MenuItem::linkToUrl('Accueil', 'fa fa-home', 'http://localhost:8082/admin');
        yield MenuItem::linkToUrl('Swagger', 'fa fa-book', 'http://localhost:8082/api');

        // Menu de gestion des profils
        yield MenuItem::section('Gestion des profils');
        yield MenuItem::subMenu('Profils', 'fa fa-user')->setSubItems([
            MenuItem::linkToCrud('Liste des profils', 'fa fa-list', Profile::class),
            MenuItem::linkToCrud('Ajouter un profil', 'fa fa-plus', Profile::class)->setAction('new'),
        ]);
        // Sous menu de gestion des avatars
        yield MenuItem::subMenu('Avatars', 'fa fa-user')->setSubItems([
            MenuItem::linkToCrud('Liste des avatars', 'fa fa-list', Avatar::class),
            MenuItem::linkToCrud('Ajouter un avatar', 'fa fa-plus', Avatar::class)->setAction('new'),
        ]);

        // Menu de gestion des images
        yield MenuItem::section('Gestion des images');
        yield MenuItem::subMenu('Images', 'fa fa-image')->setSubItems([
            MenuItem::linkToCrud('Liste des images', 'fa fa-list', Image::class),
            MenuItem::linkToCrud('Ajouter une image', 'fa fa-plus', Image::class)->setAction('new'),
        ]);

        // Menu de gestion des pièces
        yield MenuItem::section('Gestion des pièces');
        yield MenuItem::subMenu('Pièces', 'fa fa-bed')->setSubItems([
            MenuItem::linkToCrud('Liste des pièces', 'fa fa-list', Room::class),
            MenuItem::linkToCrud('Ajouter une pièce', 'fa fa-plus', Room::class)->setAction('new'),
        ]);

        // Menu de gestion des chansons
        yield MenuItem::section('Gestion des chansons');
        yield MenuItem::subMenu('Chansons', 'fa fa-music')->setSubItems([
            MenuItem::linkToCrud('Liste des chansons', 'fa fa-list', Song::class),
            MenuItem::linkToCrud('Ajouter une chanson', 'fa fa-plus', Song::class)->setAction('new'),
        ]);

        // Menu de gestion des appareils
        yield MenuItem::section('Gestion des appareils');

        // Sous menu de gestion des appareils
        yield MenuItem::subMenu('Appareils', 'fa fa-plug')->setSubItems([
            MenuItem::linkToCrud('Liste des appareils', 'fa fa-list', Device::class),
            MenuItem::linkToCrud('Ajouter un appareil', 'fa fa-plus', Device::class)->setAction('new'),
        ]);
        // Sous menu de gestion des types d'appareils
        yield MenuItem::subMenu('Types d\'appareils', 'fa fa-plug')->setSubItems([
            MenuItem::linkToCrud('Liste des types d\'appareils', 'fa fa-list', DeviceType::class),
            MenuItem::linkToCrud('Ajouter un type d\'appareil', 'fa fa-plus', DeviceType::class)->setAction('new'),
        ]);
        // Sous menu de gestion des fonctionnalités des appareils
        yield MenuItem::subMenu('Fonctionnalités des appareils', 'fa fa-cogs')->setSubItems([
            MenuItem::linkToCrud('Liste des fonctionnalités', 'fa fa-list', Feature::class),
            MenuItem::linkToCrud('Ajouter une fonctionnalité', 'fa fa-plus', Feature::class)->setAction('new'),
        ]);
        // Sous menu de gestion des paramètres des appareils
        yield MenuItem::subMenu('Paramètres des appareils', 'fa fa-cog')->setSubItems([
            MenuItem::linkToCrud('Liste des paramètres', 'fa fa-list', Setting::class),
            MenuItem::linkToCrud('Ajouter un paramètre', 'fa fa-plus', Setting::class)->setAction('new'),
        ]);

        // Menu de gestion des unités
        yield MenuItem::section('Gestion des unités');
        yield MenuItem::subMenu('Unités', 'fa fa-ruler-combined')->setSubItems([
            MenuItem::linkToCrud('Liste des unités', 'fa fa-list', Unit::class),
            MenuItem::linkToCrud('Ajouter une unité', 'fa fa-plus', Unit::class)->setAction('new'),
        ]);        
    }
}
