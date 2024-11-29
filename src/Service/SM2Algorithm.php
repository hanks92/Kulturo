<?php

namespace App\Service;

class SM2Algorithm
{
    public function calculateNextReview(
        ?float $easeFactor, 
        ?int $interval, 
        string $response, 
        array $learningSteps = [1, 10, 1440] // Étapes d'apprentissage en minutes (1m, 10m, 1j)
    ): array {
        // Initialisation des valeurs par défaut si null
        $interval = $interval ?? 0; 
        $easeFactor = $easeFactor ?? 2.5;

        // Mapper la réponse utilisateur à une qualité
        $quality = match ($response) {
            'facile' => 5,
            'correct' => 4,
            'difficile' => 3,
            'a_revoir' => 1,
            default => throw new \InvalidArgumentException("Réponse invalide : $response"),
        };

        // Gestion des étapes d'apprentissage
        if ($interval === 0 || $interval < count($learningSteps)) {
            $stepIndex = $interval;

            // Réinitialisation en cas de mauvaise réponse
            if ($quality <= 2) {
                $stepIndex = 0;
            } else {
                $stepIndex++; // Passer à l'étape suivante
            }

            // Si dans les étapes d'apprentissage
            if ($stepIndex < count($learningSteps)) {
                $nextReviewDate = (new \DateTime())->modify("+" . $learningSteps[$stepIndex] . " minutes");
                return [
                    'nextReviewDate' => $nextReviewDate,
                    'easeFactor' => $easeFactor,
                    'interval' => $stepIndex,
                ];
            }

            // Transition vers révision régulière
            $interval = 1;
        }

        // Gestion des cartes apprises
        if ($quality <= 2) {
            $interval = 1; // Réinitialisation en cas de mauvaise réponse
        } else {
            if ($interval === 1) {
                $interval = 6; // Première révision après apprentissage
            } else {
                $interval = (int) round($interval * $easeFactor);
            }
        }

        // Calcul du facteur d'aisance (ease factor)
        $easeFactor += (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        $easeFactor = max($easeFactor, 1.3); // Assurer un minimum de 1.3

        // Calculer la date de la prochaine révision
        $nextReviewDate = (new \DateTime())->modify("+$interval days");

        return [
            'nextReviewDate' => $nextReviewDate,
            'easeFactor' => $easeFactor,
            'interval' => $interval,
        ];
    }
}