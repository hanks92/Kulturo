<?php

namespace App\Form;

use App\Entity\Flashcard;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlashcardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextareaType::class, [
                'label' => 'Question',
                'required' => false,
                'attr' => [
                    'class' => 'form-control wysiwyg',
                    'placeholder' => 'Enter the question'
                ]
            ])
            ->add('answer', TextareaType::class, [
                'label' => 'Answer',
                'required' => false,
                'attr' => [
                    'class' => 'form-control wysiwyg',
                    'placeholder' => 'Enter the answer'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Flashcard::class,
        ]);
    }
}