<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Nom de la sortie : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input']
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Date de début : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input'],
                'widget' => 'single_text',
                'by_reference' => true,
                'constraints' => [
                    new GreaterThan([
                        'value' => new \DateTime(),
                        'message' => 'La date de sortie ne peut pas être antérieur à la date du jour'
                    ]),
                    new NotBlank(['message' => 'Veuillez sélectionner une date'])
                ]
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Date limite d\'inscription : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input'],
                'widget' => 'single_text',
                'by_reference' => true,
                'constraints' => [
                    new GreaterThan([
                        'value' => new \DateTime(),
                        'message' => 'La date d\'inscription ne peut pas être antérieur à la date du jour'
                    ]),
                    new NotBlank(['message' => 'Veuillez sélectionner une date']),
                    new LessThan([
                        'propertyPath' => 'parent.all[dateHeureDebut].data',
                        'message' => 'La date d\'inscription ne peut pas être supérieur à la date de début'
                    ])
                ]
            ])
            ->add('nbInscriptionsMax', IntegerType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Nombre maximum d\'inscriptions : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => [
                    'min' => 0,
                    'class' => 'sortie-form-input'
                ]
            ])
            ->add('duree', IntegerType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Durée : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => [
                    'min' => 0,
                    'class' => 'sortie-form-input'
                ]
            ])
            ->add('rue', TextType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Rue : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input disable'],
                'mapped' => false,
                'required' => false,
                'disabled' => true,
                'data' => ($builder->getData()->getLieu() !== null)
                    ? $builder->getData()->getLieu()->getRue()
                    : ''

            ])
            ->add('infosSortie', TextareaType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group text-area-group'],
                'label' => 'Description et infos',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-area']
            ])
            ->add('campus', EntityType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Campus : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input disable'],
                'class' => Campus::class,
                'multiple' => false,
                'required' => true,
                'choice_label' => 'nom'
            ])
            ->add('codePostal', TextType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Code postal : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input disable'],
                'mapped' => false,
                'required' => false,
                'data' => ($builder->getData()->getLieu() !== null)
                    ? $builder->getData()->getLieu()->getVille()->getCodePostal()
                    : ''
            ])
            ->add('latitude', TextType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Latitude : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input'],
                'mapped' => false,
                'required' => false,
                'data' => ($builder->getData()->getLieu() !== null)
                    ? $builder->getData()->getLieu()->getLatitude()
                    : ''
            ])
            ->add('longitude', TextType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label' => 'Longitude : ',
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input'],
                'mapped' => false,
                'required' => false,
                'data' => ($builder->getData()->getLieu() !== null)
                    ? $builder->getData()->getLieu()->getLongitude()
                    : ''
            ])
            ->add('enregistrer', SubmitType::class, [
                'label' => 'Enregistrer'
            ])
            ->add('publier', SubmitType::class, [
                    'label' => 'Publier'
                ]
            );

        $formModifier = function (FormInterface $form, Ville $ville = null) {
            $lieux = (null === $ville) ? [] : $ville->getLieux();
            $form->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choices' => $lieux,
                'choice_attr' => function(Lieu $lieu) {
                    return [
                        'data-rue' => $lieu->getRue(),
                        'data-latitude' => $lieu->getLatitude(),
                        'data-longitude' => $lieu->getLongitude()
                    ];
                },
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez d\'abord une ville...',
                'label' => 'Lieu : ',
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input disable'
                ],
                'multiple' => false
            ]);
        };

        if ($builder->getData()->getId() === null) {
            $builder->add('ville', EntityType::class, [
                'row_attr' => ['class' => 'sortie-form-input-group'],
                'placeholder' => 'Sélectionnez une ville',
                'label' => 'Ville : ',
                'mapped' => false,
                'required' => false,
                'class' => Ville::class,
                'multiple' => false,
                'choice_label' => 'nom',
                'choice_attr' => function(Ville $ville){
                    return [
                        'data-postal'=> $ville->getCodePostal()
                    ];
                },
                'label_attr' => ['class' => 'sortie-form-label'],
                'attr' => ['class' => 'sortie-form-input']
            ]);

            // Preset un event au chargement de la page qui charge un champ vide
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();
                    $lieu = $data->getLieu();
                    $formModifier($event->getForm(), $lieu);
                }
            );

            // Créer un évènement sur l'élément ville
            $builder->get('ville')->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $ville = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $ville);
                }
            );

        } else {
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();
                    $lieu = $data->getLieu();
                    $ville = $lieu -> getVille();
                    $formModifier($event->getForm(), $ville);
                });
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
