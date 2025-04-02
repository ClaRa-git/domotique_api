<?php

namespace App\Controller\Admin;

use App\Entity\Profile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProfileCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Profile::class;
    }

    /**
     * méthode de surchage pour configurer le crud
     * @param Crud $crud
     * @return Crud
     */
    public function configureCrud(Crud $crud): Crud
    {
        // redéfinition des titres des pages
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des profils')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un profil')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le profil');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('username', 'Nom d\'utilisateur'),
            TextField::new('password', 'Mot de passe')
        ];
    }
}
