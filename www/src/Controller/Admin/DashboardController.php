<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\Profile;
use App\Entity\Song;
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
            ->setTitle('Html');
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

        // Menu de gestion des images
        yield MenuItem::section('Gestion des images');
        yield MenuItem::subMenu('Images', 'fa fa-image')->setSubItems([
            MenuItem::linkToCrud('Liste des images', 'fa fa-list', Image::class),
            MenuItem::linkToCrud('Ajouter une image', 'fa fa-plus', Image::class)->setAction('new'),
        ]);

        // Menu de gestion des chansons
        yield MenuItem::section('Gestion des chansons');
        yield MenuItem::subMenu('Chansons', 'fa fa-music')->setSubItems([
            MenuItem::linkToCrud('Liste des chansons', 'fa fa-list', Song::class),
            MenuItem::linkToCrud('Ajouter une chanson', 'fa fa-plus', Song::class)->setAction('new'),
        ]);
    }
}
