<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class AIType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('subject', TextType::class, [
                'label' => 'Sujet du paquet de flashcards',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le sujet est requis',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Exemple : Histoire de France, Physique Quantique...',
                ],
            ])

            ->add('context', TextType::class, [
                'label' => 'Contexte ou domaine',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Exemple : Révisions Bac, Test TOEFL...',
                ],
            ])


            ->add('save', SubmitType::class, [
                'label' => 'Générer le paquet de flashcards',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
