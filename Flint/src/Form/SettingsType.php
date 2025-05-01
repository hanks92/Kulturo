<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Chemin relatif pour accéder aux avatars
        $avatarDirectory = '/assets/images/profile_pictures';
        
        // Liste des avatars (générée dynamiquement depuis le dossier)
        $avatars = scandir(__DIR__ . '/../../public' . $avatarDirectory);
        $avatarChoices = [];

        $avatarCounter = 1;
        foreach ($avatars as $avatar) {
            if (pathinfo($avatar, PATHINFO_EXTENSION) === 'png') {
                $avatarChoices['Avatar ' . $avatarCounter] = $avatarDirectory . '/' . $avatar;
                $avatarCounter++;
            }
        }

        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                'attr' => [
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white'
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => false,       // Rendre le mot de passe optionnel
                'mapped' => false,          // Ne pas lier directement à l’entité User
                'attr' => [
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white'
                ]
            ])
            ->add('profileImage', ChoiceType::class, [
                'label' => 'Choisissez votre avatar',
                'choices' => $avatarChoices,
                'expanded' => true, // Afficher sous forme de boutons radio
                'multiple' => false,
                'attr' => [
                    'class' => 'grid grid-cols-4 gap-2'
                ]
            ])
            ->add('theme', ChoiceType::class, [
                'label' => 'Choisissez un thème',
                'choices' => [
                    'Light mode' => 'light',
                    'Dark mode' => 'dark',
                ],
                'expanded' => true, // Afficher sous forme de boutons radio
                'multiple' => false,
                'attr' => [
                    'class' => 'flex space-x-4'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
