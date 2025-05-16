<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserStats;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api_')]
class ApiRegistrationController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager // ✅ Injection du service Lexik JWT
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email'], $data['username'], $data['password'])) {
            return new JsonResponse(['error' => 'Les champs email, username et password sont requis.'], Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Cet email est déjà utilisé.'], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        if (!empty($data['profileImage'])) {
            $user->setProfileImage($data['profileImage']);
        }

        // Création des stats utilisateur
        $stats = new UserStats();
        $stats->setUser($user);
        $stats->setStreak(0);
        $stats->setMaxStreak(0);
        $stats->setTotalXp(0);
        $stats->setCardsReviewed(0);
        $stats->setLastActivity(null);
        $user->setStats($stats);

        $em->persist($user);
        $em->flush();

        // ✅ Génère le token JWT
        $token = $jwtManager->create($user);

        return new JsonResponse([
            'message' => 'Inscription réussie.',
            'token' => $token,
        ], Response::HTTP_CREATED);
    }
}
