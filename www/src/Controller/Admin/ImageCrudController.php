<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageCrudController extends AbstractCrudController
{
    public const AVATAR_BASE_PATH = 'images/avatars';
    public const AVATAR_UPLOAD_DIR = 'public/images/avatars';

    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des images')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une image')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l\'image');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            ImageField::new('image_path', 'Image')
                ->setBasePath(self::AVATAR_BASE_PATH)
                ->setUploadDir(self::AVATAR_UPLOAD_DIR)
                ->setUploadedFileNamePattern(
                    fn (UploadedFile $file): string => sprintf(
                        'upload_%d_%s.%s',
                        random_int(1, 999),
                        $file->getFilename(),
                        $file->guessExtension()
                    )
                )
        ];
    }
}
