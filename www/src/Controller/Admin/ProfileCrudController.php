<?php

namespace App\Controller\Admin;

use App\Entity\Avatar;
use App\Entity\Profile;
use App\Repository\AvatarRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProfileCrudController extends AbstractCrudController
{
    private AvatarRepository $avatarRepository;

    public function __construct(AvatarRepository $avatarRepository)
    {
        $this->avatarRepository = $avatarRepository;
    }

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
        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('username', 'Nom d\'utilisateur'),
            TextField::new('password', 'Mot de passe')
        ];
    
        if ($pageName === Crud::PAGE_INDEX) {
            $fields[] = ImageField::new('avatar.imagePath', 'Avatar')
                ->setBasePath('/images/avatars');
        } else {
            // Récupération des avatars depuis la BDD
            $avatars = $this->avatarRepository->findAll();
    
            // Construction des choix (ID => HTML <img>)
            $choices = [];
            foreach ($avatars as $avatar) {
                $choices[
                    '<img src="/images/avatars/' . $avatar->getImagePath() . '" height="50">'
                ] = $avatar->getId();
            }
    
            $fields[] = AssociationField::new('avatar', 'Avatar')
                ->setFormTypeOptions([
                    'choices' => $this->avatarRepository->findAll(),
                    'choice_label' => function ($avatar) {
                        return $avatar->getImagePath(); // ou autre champ si besoin
                    },
                    'choice_value' => 'id',
                ]);
        }
    
        return $fields;
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
