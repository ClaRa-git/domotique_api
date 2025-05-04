<?php

namespace App\Controller\Admin;

use App\Entity\DefaultSetting;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DefaultSettingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DefaultSetting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        // redéfinition des titres des pages
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des paramètres par défaut')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un paramètre')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier un paramètre');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('value', 'Valeur'),
            AssociationField::new('feature', 'Fonctionnalité')
                ->setFormTypeOption('choice_value', 'id'),
            AssociationField::new('device', 'Appareil')
                ->setFormTypeOption('choice_value', 'id')            
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // redéfinition des boutons d'actions de la page d'index
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $action) => $action
                    ->setIcon('fa fa-plus')
                    ->setLabel('Ajouter')
                    ->setCssClass('btn btn-primary')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn (Action $action) => $action
                    ->setIcon('fa fa-pen')
                    ->setLabel('Modifier')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn (Action $action) => $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            // redéfinition des boutons d'actions de la page édit
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn (Action $action) => $action
                    ->setLabel('Enregistrer et quitter')
            )                 
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE,
                fn (Action $action) => $action
                    ->setLabel('Enregistrer et continuer')
            )
            // redéfinition des boutons d'actions de la page new
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn (Action $action) => $action
                    ->setLabel('Enregistrer et quitter')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn (Action $action) => $action
                    ->setLabel('Enregistrer et ajouter')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn (Action $action) => $action
                    ->setIcon('fa fa-eye')
                    ->setLabel('Voir')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                fn (Action $action) => $action
                    ->setIcon('fa fa-pen')
                    ->setLabel('Modifier')
            )
            ->remove(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn (Action $action) => $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::INDEX,
                fn (Action $action) => $action
                    ->setIcon('fa fa-list')
                    ->setLabel('Retour à la liste')
            );
    }
}
