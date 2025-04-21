<?php

namespace App\Controller;

use App\Repository\UserStatsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'root_redirect')]
    public function rootRedirect(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    #[Route('/home', name: 'app_home')]
    public function index(UserStatsRepository $userStatsRepository): Response
    {
        $user = $this->getUser();
        $userStats = $user ? $userStatsRepository->findOneBy(['user' => $user]) : null;

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'userWater' => $userStats ? $userStats->getWater() : 0,
        ]);
    }
}
