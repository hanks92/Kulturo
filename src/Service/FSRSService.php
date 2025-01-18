<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class FSRSService
{
    private string $fsrsScriptPath;
    private LoggerInterface $logger;

    public function __construct(string $fsrsScriptPath, LoggerInterface $logger)
    {
        $this->fsrsScriptPath = $fsrsScriptPath;
        $this->logger = $logger;
    }

    /**
     * Appelle le script Node.js pour réviser une carte avec FSRS.
     *
     * @param array $cardData Données de la carte (JSON serializable).
     * @param string $userRating Évaluation utilisateur (e.g., "again", "hard", "good", "easy").
     * @return array Résultats calculés par FSRS.
     * @throws \RuntimeException En cas d'échec du script ou d'erreur dans le résultat.
     */
    public function reviewCard(array $cardData, string $userRating): array
    {
        // Valider l'évaluation utilisateur
        if (!in_array($userRating, ['again', 'hard', 'good', 'easy'], true)) {
            throw new \InvalidArgumentException('Invalid user rating provided.');
        }

        // Valider les données de la carte
        $this->validateCardData($cardData);

        // Construire la commande pour exécuter le script Node.js
        $command = sprintf(
            'node %s %s %s',
            escapeshellarg($this->fsrsScriptPath),
            escapeshellarg(json_encode($cardData, JSON_THROW_ON_ERROR)),
            escapeshellarg($userRating)
        );

        // Exécuter le script Node.js
        $this->logger->info('Executing FSRS script', ['command' => $command]);

        $output = shell_exec($command);

        // Vérifier si la commande a échoué
        if ($output === null || $output === '') {
            $this->logger->error('FSRS script execution failed', ['command' => $command]);
            throw new \RuntimeException('FSRS script execution failed.');
        }

        // Convertir la sortie en tableau PHP
        $result = json_decode($output, true);

        // Vérifier si la sortie JSON est valide
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON output from FSRS script', ['output' => $output]);
            throw new \RuntimeException('Invalid JSON output from FSRS script.');
        }

        // Valider les résultats retournés par FSRS
        $this->validateFSRSResult($result);

        $this->logger->info('FSRS script executed successfully', ['result' => $result]);

        return $result;
    }

    /**
     * Valide la structure des données de la carte.
     *
     * @param array $cardData
     * @throws \InvalidArgumentException Si les données sont invalides.
     */
    private function validateCardData(array $cardData): void
    {
        $requiredKeys = ['card_id', 'state', 'stability', 'difficulty', 'last_review'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $cardData)) {
                throw new \InvalidArgumentException(sprintf('Missing key "%s" in card data.', $key));
            }
        }

        if (!is_int($cardData['card_id']) && !is_null($cardData['card_id'])) {
            throw new \InvalidArgumentException('Invalid type for "card_id". Expected int or null.');
        }

        if (!is_int($cardData['state']) && !is_null($cardData['state'])) {
            throw new \InvalidArgumentException('Invalid type for "state". Expected int or null.');
        }

        if (!is_float($cardData['stability']) && !is_null($cardData['stability'])) {
            throw new \InvalidArgumentException('Invalid type for "stability". Expected float or null.');
        }

        if (!is_float($cardData['difficulty']) && !is_null($cardData['difficulty'])) {
            throw new \InvalidArgumentException('Invalid type for "difficulty". Expected float or null.');
        }

        if (!is_string($cardData['last_review']) && !is_null($cardData['last_review'])) {
            throw new \InvalidArgumentException('Invalid type for "last_review". Expected string or null.');
        }
    }

    /**
     * Valide les résultats retournés par FSRS.
     *
     * @param array $result
     * @throws \RuntimeException Si les résultats sont invalides.
     */
    private function validateFSRSResult(array $result): void
    {
        $requiredKeys = ['stability', 'difficulty', 'last_review', 'due', 'state'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $result)) {
                throw new \RuntimeException(sprintf('Missing key "%s" in FSRS result.', $key));
            }
        }
    }
}
