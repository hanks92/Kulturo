<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request; // Import de Request
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; // Utilisation de l'annotation Route
use App\Form\FlintType; // Import du formulaire FlintType

class AppController extends AbstractController
{
    #[Route('/app', name: 'app_app')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(FlintType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dd($data);
        }

        return $this->render('app/index.html.twig', [
            'controller_name' => 'AppController',
            'form' => $form,
        ]);
    }
}
