<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class AIDeckFlashcardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Sujet (texte libre)
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

            // Champ Contexte/Domaine (texte libre)
            ->add('context', TextType::class, [
                'label' => 'Contexte ou domaine',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Exemple : Révisions Bac, Test TOEFL...',
                ],
            ])

            // Champ Upload de documents
            ->add('documents', FileType::class, [
                'label' => 'Uploader des documents (PDF, TXT, DOCX)',
                'multiple' => true,
                'mapped' => false, // Important : ce champ n'est pas lié à une entité
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF, TXT ou DOCX valide.',
                    ]),
                ],
            ])

            // Bouton de soumission
            ->add('save', SubmitType::class, [
                'label' => 'Générer le paquet de flashcards',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
