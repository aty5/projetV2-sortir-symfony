<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mail',EmailType::class, ['label' => 'Email : '])
            ->add('motPasse', PasswordType::class,
                [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'required' => false,
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Mot de passe : '
            ])

            ->add('confirmation', PasswordType::class,
                [
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'required' => false,
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'label' => 'Confirmation : '

            ])
            ->add('nom', TextType::class, ['label' => 'Nom : '])
            ->add('prenom', TextType::class, ['label' => 'Prénom : '])
            ->add('telephone', TelType::class, ['label' => 'Téléphone : '])
            ->add('pseudo', TextType::class, ['label' => 'Pseudo : '])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'multiple' => false,
                'required' => true,
                'choice_label' => 'nom',
                'label' => 'Campus : '])
            ->add('enregister', SubmitType::class, [ 'label' => 'Enregister'])
        ;
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
