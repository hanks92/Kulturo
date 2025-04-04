<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AIType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Field for the deck title
            ->add('title', TextType::class, [
                'label' => 'Deck Title',
                'constraints' => [
                    new NotBlank([
                        'message' => 'The deck title is required.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'e.g. Medieval History, Cell Biology...',
                ],
            ])

            ->add('subject', TextType::class, [
                'label' => 'Flashcard Pack Subject',
                'constraints' => [
                    new NotBlank([
                        'message' => 'The subject is required.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'e.g. French History, Quantum Physics...',
                ],
            ])

            ->add('context', TextType::class, [
                'label' => 'Context or Domain',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g. High School Exam Prep, TOEFL Practice...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
