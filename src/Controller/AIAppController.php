<?php

namespace App\Controller;

use App\Form\AIDeckFlashcardType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AIAppController extends AbstractController
{
    #[Route('/aiapp', name: 'app_app')]
    public function index(Request $request): Response
    {
        // Création du formulaire
        $form = $this->createForm(AIDeckFlashcardType::class);
        
        // Gestion de la requête HTTP (soumission du formulaire)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des données du formulaire
            $data = $form->getData();

            // Debug pour voir les données
            dd($data);
        }

        return $this->render('aiapp/index.html.twig', [
            'controller_name' => 'AIAppController',
            'form' => $form->createView(),
        ]);
    }
}
