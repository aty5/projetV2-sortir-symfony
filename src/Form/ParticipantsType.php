<?php

namespace App\Form;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('participants', EntityType::class, [
                'class' => Participant::class,
                'query_builder' => function(ParticipantRepository $pr) {
                    return $pr->createQueryBuilder('p')
                        ->addOrderBy('p.nom', 'ASC')
                        ->addOrderBy('p.prenom', 'ASC');
                },
                'choice_label' => function($choice) {
                    return ' ' . strtoupper($choice->getNom()) . ' ' . $choice->getPrenom() . ' (' .
                        ($choice->isActif() ? 'actif' : 'désactivé') . ')';
                },
                'choice_attr' => function() { return ['class' => 'checkbox-participant']; },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Liste des participant.e.s',
                'block_prefix' => 'choix_participants',
                'block_name' => 'choix_participants',
            ])
            ->add('desactiver', SubmitType::class, [
                'label' => 'Désactiver',
                'attr' => ['onclick' => 'return confirm("Êtes-vous certain de vouloir désactiver les utilisateurs et utilisatrices sélectionnés ?")'],
            ])
            ->add('activer', SubmitType::class, [
                'attr' => ['onclick' => 'return confirm("Êtes-vous certain de vouloir activer les utilisateurs et utilisatrices sélectionnés ?")'],
            ])
            ->add('supprimer', SubmitType::class, [
                'attr' => ['onclick' => 'return confirm("Êtes-vous certain de vouloir supprimer les utilisateurs et utilisatrices sélectionnés ?")']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
