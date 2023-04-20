<?php

namespace App\Controller;

use Imagine\Gd\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ImageController extends AbstractController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return[
            TextField::new('imageFile')->setFormType(VichImageType::class)

        ];
    }


}
