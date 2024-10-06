<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FlintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Sujet du paquet (obligatoire)
            ->add('subject', TextType::class, [
                'label' => 'Sujet du paquet',
                'attr' => [
                    'placeholder' => 'Entrez le sujet du paquet'
                ],
            ])

            // Description du paquet (optionnel)
            ->add('description', TextareaType::class, [
                'label' => 'Description du paquet',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description courte du paquet (optionnel)'
                ],
            ])

            // Nombre de cartes à générer (obligatoire)
            ->add('num_cards', IntegerType::class, [
                'label' => 'Nombre de cartes à générer',
                'attr' => [
                    'min' => 1,
                    'placeholder' => 'Combien de cartes voulez-vous générer ?'
                ],
            ])

            // Niveau de difficulté (optionnel)
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Niveau de difficulté',
                'choices' => [
                    'Débutant' => 'beginner',
                    'Intermédiaire' => 'intermediate',
                    'Avancé' => 'advanced',
                ],
                'required' => false,
                'placeholder' => 'Sélectionnez un niveau (optionnel)',
            ])

            // Type de questions (optionnel)
            ->add('question_type', ChoiceType::class, [
                'label' => 'Type de questions',
                'choices' => [
                    'Choix multiple' => 'multiple_choice',
                    'Questions ouvertes' => 'open_ended',
                    'Vrai/Faux' => 'true_false',
                ],
                'required' => false,
                'placeholder' => 'Sélectionnez le type de questions (optionnel)',
            ])

            // Catégorie du paquet (optionnel)
            ->add('category', TextType::class, [
                'label' => 'Catégorie du paquet',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Exemple: Langue, Histoire, etc.'
                ],
            ])

            // Bouton de soummission du formulaire
            ->add("submit", SubmitType::class, [
                "label" => "Générer mon packet de cartes",
                "attr" => [
                    "class" => "btn btn-primary",
                ],
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
