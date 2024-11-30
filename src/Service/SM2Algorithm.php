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

        // Mapper la réponse utilisateur à une qualité (de 0 à 5)
        $quality = match ($response) {
            'facile' => 5,
            'correct' => 4,
            'difficile' => 3,
            'a_revoir' => 1,
            default => throw new \InvalidArgumentException("Réponse invalide : $response"),
        };

        // Si qualité très basse, réinitialisation immédiate
        if ($quality <= 2) {
            $interval = 0; // Réinitialisation des étapes d'apprentissage
            $easeFactor = max($easeFactor - 0.2, 1.3); // Réduction du facteur d'aisance
            $nextReviewDate = (new \DateTime())->modify("+{$learningSteps[0]} minutes");
            return [
                'nextReviewDate' => $nextReviewDate,
                'easeFactor' => $easeFactor,
                'interval' => $interval,
            ];
        }

        // Gestion des étapes d'apprentissage
        if ($interval < count($learningSteps)) {
            // Recalcul du facteur d'aisance
            $easeFactor += (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
            $easeFactor = max($easeFactor, 1.3); // Assurer un minimum de 1.3

            $nextInterval = $interval + 1; // Progression dans les étapes
            $nextReviewDate = (new \DateTime())->modify("+" . $learningSteps[$nextInterval] . " minutes");
            return [
                'nextReviewDate' => $nextReviewDate,
                'easeFactor' => $easeFactor,
                'interval' => $nextInterval,
            ];
        }

        // Gestion des révisions régulières pour les cartes apprises
        if ($interval >= count($learningSteps)) {
            // Calcul du nouvel intervalle
            if ($interval === 1) {
                $interval = 6; // Première révision
            } else {
                $interval = (int) round($interval * $easeFactor);
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

        // Cas par défaut si aucune règle ne s'applique
        throw new \LogicException('Calcul SM2 inattendu.');
    }
}
