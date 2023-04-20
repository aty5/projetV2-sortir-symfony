<?php

namespace App\Form;

use App\Data\Filtre;
use App\Entity\Campus;
use App\Entity\Sortie;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', SearchType::class,[
                'label'=> 'Rechercher une sortie par mots-clé :    ',
                'required'=>false,
                'attr'=>[
                    'placeholder'=> 'ex: paddle, cinéma ... ']
        ])
            ->add('campus', EntityType::class,[

                'label'=> 'Choix du campus :     ',
                'choice_label'=>'nom',
                'class' => Campus::class,
                'required'=>false,
            ])
            ->add('dateDebutRecherche', DateType::class,[
                'html5'=> true,
                'widget'=>'single_text',
                'required'=>false,
                'label'=> 'Voir les sorties entre le '
            ])

            ->add('dateFinRecherche', DateType::class,[
                'html5'=> true,
                'widget'=>'single_text',
                'label'=> ' et le ',
                'required'=>false
            ])

            ->add('organisateur', CheckboxType::class,[
                'label'=> 'Sorties dont je suis l\' organisateur/trice  ',
                'required'=>false
            ])

            ->add('inscrit', CheckboxType::class,[
                'label'=> 'Sorties auxquelles je suis inscrit/e  ',
                'required'=>false
            ])

            ->add('pasInscrit', CheckboxType::class,[
                'label'=> 'Sorties auxquelles je ne suis pas inscrit/e  ',
                'required'=>false
            ])

            ->add('sortiesPassees', CheckboxType::class,[
                'label'=> 'Sorties passées  ',
                'required'=>false
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'POST'
        ]);
    }
}
