<?php

namespace App\Service;

class FSRSAlgorithm
{
    public function calculateNextReview(array $cardData, string $userRating): array
    {
        // Construire la commande pour appeler le script Node.js
        $command = sprintf(
            'node public/build/fsrs.js "%s" "%s"',
            escapeshellarg(json_encode($cardData)), // Données de la carte en JSON
            escapeshellarg($userRating) // Réponse utilisateur (e.g., "Good", "Hard")
        );

        // Exécuter le script Node.js
        $output = shell_exec($command);

        if ($output === null || $output === '') {
            throw new \RuntimeException('FSRS script execution failed.');
        }

        // Retourner les résultats sous forme de tableau PHP
        return json_decode($output, true);
    }
}
