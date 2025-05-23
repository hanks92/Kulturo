<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AIType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Titre du Deck
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
            // Prompt utilisateur (demande de détails à l'utilisateur)
            ->add('prompt', TextareaType::class, [
                'label' => 'Prompt for AI',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please provide a prompt for the AI.',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Describe precisely what you want the AI to generate...',
                    'rows' => 5,
                ],
            ])
            // Ressources supplémentaires (optionnel)
            ->add('resources', TextareaType::class, [
                'label' => 'Additional Resources',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Provide links, references, or additional resources...',
                    'rows' => 4,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
