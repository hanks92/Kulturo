<?php

namespace App\Service;

class SM2Algorithm
{
    public function calculateNextReview(
        ?float $easeFactor, 
        ?int $interval, 
        string $response, 
        ?float $stability = 1.0, 
        ?float $retrievability = 0.9
    ): array {
        // Initialisation des valeurs par défaut si elles sont nulles
        $easeFactor = $easeFactor ?? 2.5;
        $interval = $interval ?? 0;
        $stability = $stability ?? 1.0;
        $retrievability = $retrievability ?? 0.9;

        // Mapper la réponse utilisateur à une qualité entre 0 et 1
        $quality = match ($response) {
            'facile' => 1.0,  // Réponse parfaite
            'correct' => 0.8, // Bonne réponse
            'difficile' => 0.6, // Réponse difficile
            'a_revoir' => 0.0,  // À revoir (spécial)
            default => throw new \InvalidArgumentException("Réponse invalide : $response"),
        };

        // Cas spécial : "À revoir"
        if ($response === 'a_revoir') {
            // Réinitialiser la carte pour la revoir rapidement
            $stability = max($stability * 0.5, 0.1); // Réduire significativement la stabilité
            $retrievability = 0.01; // Supposer un oubli quasi-total
            $nextInterval = 1 / 1440; // Fixer à 1 minute (1/1440 jour)

            // Assurer que l'intervalle est en minutes dans le retour
            $nextIntervalMinutes = max(1, round($nextInterval * 1440)); // Minimum 1 minute

            $nextReviewDate = (new \DateTime())->modify("+$nextIntervalMinutes minutes");

            return [
                'nextReviewDate' => $nextReviewDate,
                'easeFactor' => max($easeFactor - 0.2, 1.3), // Diminue légèrement le easeFactor
                'interval' => $nextIntervalMinutes, // Intervalle en minutes
                'stability' => $stability,
                'retrievability' => $retrievability,
            ];
        }

        // Mise à jour de retrievability en fonction de la stabilité et de l'intervalle
        if ($interval > 0) {
            $retrievability = exp(-$interval / $stability);
        }

        // Calcul du multiplicateur de stabilité en fonction de la qualité
        $stabilityMultiplier = match (true) {
            $quality >= 0.9 => 1.2,  // Parfait : augmentation rapide
            $quality >= 0.6 => 1.0,  // Correct : progression normale
            $quality >= 0.3 => 0.8,  // Difficile : progression lente
            default => 0.5,          // Oublié : réduction importante
        };

        // Mise à jour de la stabilité
        $stability *= $stabilityMultiplier;

        // Ajustement du easeFactor en fonction de la réponse
        $easeFactor = match ($response) {
            'facile' => min($easeFactor + 0.15, 3.0), // Augmentation pour "facile"
            'correct' => $easeFactor, // Inchangé pour "correct"
            'difficile' => max($easeFactor - 0.15, 1.3), // Réduction pour "difficile"
            default => $easeFactor,
        };

        // Calcul de l'intervalle avec des limites pour éviter des valeurs extrêmes
        $nextInterval = max(0.01, min($stability * log(1 / max($retrievability, 0.01)), 3650)); // Limité à 10 ans max
        $nextReviewDate = (new \DateTime())->modify("+$nextInterval days");

        return [
            'nextReviewDate' => $nextReviewDate,
            'easeFactor' => $easeFactor,  // EaseFactor mis à jour
            'interval' => round($nextInterval, 5),  // Intervalle arrondi en jours
            'stability' => $stability,  // Nouvelle stabilité
            'retrievability' => $retrievability,  // Nouvelle probabilité de rappel
        ];
    }
}
