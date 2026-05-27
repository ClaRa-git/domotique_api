<?php

namespace App\Controller\Admin;

use App\Entity\Avatar;
use App\Entity\Profile;
use App\Repository\AvatarRepository;
use Doctrine\ORM\EntityManagerInterface;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileCrudController extends AbstractCrudController
{
    private AvatarRepository $avatarRepository;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        AvatarRepository $avatarRepository,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->avatarRepository = $avatarRepository;
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return Profile::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
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
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setHelp($pageName === Crud::PAGE_EDIT ? 'Laisser vide pour ne pas modifier le mot de passe' : ''),
        ];

        if ($pageName === Crud::PAGE_INDEX) {
            $fields[] = ImageField::new('avatar.imagePath', 'Avatar')
                ->setBasePath('/images/avatars');
        } else {
            $avatars = $this->avatarRepository->findAll();

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
                        return $avatar->getImagePath();
                    },
                    'choice_value' => 'id',
                ]);
        }

        return $fields;
    }

    /**
     * Hachage du mot de passe à la création
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Profile && $entityInstance->getPassword()) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword())
            );
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * Hachage du mot de passe à la modification — uniquement si une nouvelle valeur est saisie
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Profile) {
            $plain = $entityInstance->getPassword();
            if ($plain && !str_starts_with($plain, '$2y$') && !str_starts_with($plain, '$argon')) {
                $entityInstance->setPassword(
                    $this->passwordHasher->hashPassword($entityInstance, $plain)
                );
            }
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW,
                fn (Action $action) => $action->setIcon('fa fa-plus')->setLabel('Ajouter')->setCssClass('btn btn-primary')
            )
            ->update(Crud::PAGE_INDEX, Action::EDIT,
                fn (Action $action) => $action->setIcon('fa fa-pen')->setLabel('Modifier')
            )
            ->update(Crud::PAGE_INDEX, Action::DELETE,
                fn (Action $action) => $action->setIcon('fa fa-trash')->setLabel('Supprimer')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN,
                fn (Action $action) => $action->setLabel('Enregistrer et quitter')
            )
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE,
                fn (Action $action) => $action->setLabel('Enregistrer et continuer')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                fn (Action $action) => $action->setLabel('Enregistrer et quitter')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                fn (Action $action) => $action->setLabel('Enregistrer et ajouter')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL,
                fn (Action $action) => $action->setIcon('fa fa-eye')->setLabel('Voir')
            )
            ->update(Crud::PAGE_DETAIL, Action::EDIT,
                fn (Action $action) => $action->setIcon('fa fa-pen')->setLabel('Modifier')
            )
            ->remove(Crud::PAGE_DETAIL, Action::DELETE,
                fn (Action $action) => $action->setIcon('fa fa-trash')->setLabel('Supprimer')
            )
            ->update(Crud::PAGE_DETAIL, Action::INDEX,
                fn (Action $action) => $action->setIcon('fa fa-list')->setLabel('Retour à la liste')
            );
    }
}