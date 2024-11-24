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
        // Valeurs par défaut pour une nouvelle carte
        if ($interval === null) {
            $interval = 0; // Étape initiale d'apprentissage
        }
        if ($easeFactor === null) {
            $easeFactor = 2.5; // Facteur d'aisance initial
        }

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
            // "Interval" agit comme un index pour les étapes d'apprentissage
            $stepIndex = $interval; // Étape actuelle
            if ($quality <= 2) {
                $stepIndex = 0; // Réinitialisation en cas de mauvaise réponse
            } else {
                $stepIndex++; // Passer à l'étape suivante
            }

            // Si encore dans les étapes d'apprentissage
            if ($stepIndex < count($learningSteps)) {
                $nextReviewDate = (new \DateTime())->modify("+" . $learningSteps[$stepIndex] . " minutes");
                return [
                    'nextReviewDate' => $nextReviewDate,
                    'easeFactor' => $easeFactor,
                    'interval' => $stepIndex,
                ];
            }

            // Si toutes les étapes d'apprentissage sont terminées
            $interval = 1; // Première révision "apprise"
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

