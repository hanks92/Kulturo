<?php

namespace App\Service;

class SM2Algorithm
{
    public function calculateNextReview(?float $easeFactor, ?int $interval, string $response): array
    {
        // Valeurs par défaut pour une nouvelle carte
        if ($interval === null) {
            $interval = 1;
        }
        if ($easeFactor === null) {
            $easeFactor = 2.5; // Facteur d'aisance initial
        }

        // Mapper la réponse utilisateur à une qualité
        $quality = match ($response) {
            'facile' => 5,
            'correct' => 4,
            'difficile' => 3,
            'a_revoir' => 2,
            default => throw new \InvalidArgumentException("Réponse invalide : $response"),
        };

        // Calculer l'intervalle suivant
        if ($quality <= 2) {
            $interval = 1; // Mauvaise réponse, réinitialisation
        } else {
            if ($interval === 1) {
                $interval = 6;
            } else {
                $interval = (int) round($interval * $easeFactor);
            }
        }

        // Calculer le nouveau facteur d'aisance
        $easeFactor += (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));

        // Assurer un facteur d'aisance minimum de 1.3
        $easeFactor = max($easeFactor, 1.3);

        // Calculer la date de la prochaine révision
        $nextReviewDate = (new \DateTime())->modify("+$interval days");

        return [
            'nextReviewDate' => $nextReviewDate,
            'easeFactor' => $easeFactor,
            'interval' => $interval,
        ];
    }
}
