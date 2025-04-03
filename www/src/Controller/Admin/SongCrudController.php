<?php

namespace App\Controller\Admin;

use getID3;
use App\Entity\Song;
use Doctrine\ORM\EntityManagerInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File as FileConstraint;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class SongCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Song::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des chansons')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier une chanson')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une chanson');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre de la chanson'),
            TextField::new('artist', 'Artiste'),
            Field::new('filePathFile', 'Choisir mp3')
                ->onlyOnForms()
                ->setFormType(VichFileType::class)
                ->setFormTypeOptions([
                    'constraints' => [
                        new FileConstraint([
                            'maxSize' => '10M',
                            'mimeTypes' => [
                                'audio/mpeg',
                                'audio/mp3',
                                'audio/m4a'
                            ],
                            'mimeTypesMessage' => 'Veuillez télécharger un mp3 valide',
                            'maxSizeMessage' => 'Le fichier est trop volumineux',
                        ]),
                    ],
                ]),
            TextField::new('file_path', 'Aperçu')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    return '<audio controls>
                                <source src="/upload/files/music/' . $value . '" type="audio/mpeg">
                            </audio>';
                }),
            NumberField::new('duration', 'Durée du titre')
                ->hideOnForm()
                // on convertit la durée en mm:ss
                ->formatValue(function ($value, $entity) {
                    if ($value === null) {
                        return '00:00';
                    }
                    $hours = floor($value / 3600);
                    $minutes = floor(($value % 3600) / 60);
                    $seconds = $value % 60;

                    // Si la durée est supérieure à 1 heure, on affiche le format hh:mm:ss
                    if ($hours > 0) {
                        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    }
                    // Sinon, on affiche le format mm:ss / m:ss
                     else {
                        return sprintf('%d:%02d', $minutes, $seconds);
                    }
                }),
                
        ];
    }


    //function pour agir sur les boutons d'actions
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            //permet de customiser les boutons de la page index
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn(Action $action) => $action
                    ->setIcon('fa fa-add')
                    ->setLabel('Ajouter')
                    ->setCssClass('btn btn-success')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::EDIT,
                fn(Action $action) => $action
                    ->setIcon('fa fa-pen')
                    ->setLabel('Modifier')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::DELETE,
                fn(Action $action) => $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::BATCH_DELETE,
                fn(Action $action) => $action
                    ->setIcon('fa fa-trash')
                    ->setLabel('Supprimer')
            )
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(
                Crud::PAGE_INDEX,
                Action::DETAIL,
                fn(Action $action) => $action->setIcon('fa fa-info')->setLabel('Informations')
            )
            //customiser les boutons de la page édition
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_RETURN,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et quitter')
            )
            ->update(
                Crud::PAGE_EDIT,
                Action::SAVE_AND_CONTINUE,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et continuer')
            )
            //Page de création
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_RETURN,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer')
            )
            ->update(
                Crud::PAGE_NEW,
                Action::SAVE_AND_ADD_ANOTHER,
                fn(Action $action) => $action
                    ->setLabel('Enregistrer et ajouter un nouveau')
            )
            //pade de détail
            ->update(
                Crud::PAGE_DETAIL,
                Action::DELETE,
                fn(Action $action) => $action
                    ->setLabel('Supprimer')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::INDEX,
                fn(Action $action) => $action
                    ->setLabel('Retour à la liste')
            )
            ->update(
                Crud::PAGE_DETAIL,
                Action::EDIT,
                fn(Action $action) => $action
                    ->setLabel('Modifier')
            );
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Song) return;
        $file = $entityInstance->getFilePathFile();
        $entityInstance->setDuration($this->getDurationFile($file));
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Song) return;
        $file = $entityInstance->getFilePathFile();
        $entityInstance->setDuration($this->getDurationFile($file));

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function getDurationFile(UploadedFile $file): int
    {
        $getId3 = new getID3();

        // Utiliser le chemin temporaire du fichier directement
        $filePath = $file->getRealPath();

        // Vérifiez si le fichier temporaire existe
        if (!file_exists($filePath)) {
            return 0; // Fichier introuvable
        }

        try {
            // Analyse du fichier
            $analysis = $getId3->analyze($filePath);

            // Retourner la durée du fichier
            return $analysis['playtime_seconds'] ?? 0;
        } catch (\Exception $e) {
            // En cas d'erreur, vous pouvez logger l'exception ou la gérer autrement
            // Exemple : $this->logger->error($e->getMessage());
            return 0; // Erreur d'analyse
        }
    }
}