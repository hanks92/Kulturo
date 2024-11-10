<?php

namespace App\Controller;

use App\Form\SettingsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Créer le formulaire et lier les données de l'utilisateur
        $form = $this->createForm(SettingsType::class, $user);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            // Mise à jour du mot de passe si un nouveau mot de passe est renseigné
            $newPassword = $form->get('plainPassword')->getData();
            if ($newPassword) {
                $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($encodedPassword);
            }

            // Gestion du téléchargement de la photo de profil
            /** @var UploadedFile $profileImageFile */
            $profileImageFile = $form->get('profileImage')->getData();
            if ($profileImageFile) {
                $newFilename = uniqid() . '.' . $profileImageFile->guessExtension();

                try {
                    $profileImageFile->move(
                        $this->getParameter('profile_images_directory'),
                        $newFilename
                    );
                    $user->setProfileImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image de profil.');
                }
            }

            // Enregistrer les modifications dans la base de données
            $entityManager->flush();

            $this->addFlash('success', 'Paramètres mis à jour avec succès !');
            return $this->redirectToRoute('app_settings');
        }

        return $this->render('settings/settings.html.twig', [
            'settingsForm' => $form->createView(),        
        ]);
    }
}
