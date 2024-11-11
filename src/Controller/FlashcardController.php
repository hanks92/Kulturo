<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FlashcardController extends AbstractController
{
    #[Route('/flashcard', name: 'app_flashcard')]
    public function index(): Response
    {
        return $this->render('flashcard/index.html.twig', [
            'controller_name' => 'FlashcardController',
        ]);
    }
}
