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

        // Vérification explicite pour éviter les erreurs
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }
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

            // Gestion du choix de l'avatar (pas d'upload, mais un chemin prédéfini)
            $selectedAvatar = $form->get('profileImage')->getData();
            if ($selectedAvatar) {
                $user->setProfileImage($selectedAvatar);
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
