<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class FSRSService
{
    private string $fsrsScriptPath;
    private LoggerInterface $logger;
    private string $flaskApiUrl; // URL de l'API Flask

    public function __construct(string $fsrsScriptPath, LoggerInterface $logger, string $flaskApiUrl)
    {
        $this->fsrsScriptPath = $fsrsScriptPath;
        $this->logger = $logger;
        $this->flaskApiUrl = $flaskApiUrl;
    }

    /**
     * Initialise les paramètres d'une carte via l'API Flask FSRS.
     *
     * @return array Les paramètres initiaux pour FSRS.
     * @throws \RuntimeException En cas d'échec de la requête ou de l'API Flask.
     */
    public function initializeCard(): array
    {
        $this->logger->info('Requesting FSRS initial parameters from Flask API');

        // Préparer l'URL de l'API Flask
        $endpoint = $this->flaskApiUrl . '/initialize';

        // Effectuer la requête HTTP
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            $this->logger->error('Failed to fetch initial parameters from Flask API', [
                'http_code' => $httpCode,
                'response' => $response,
            ]);
            throw new \RuntimeException('Unable to fetch initial parameters from FSRS Flask API.');
        }

        // Convertir la réponse JSON en tableau PHP
        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON received from FSRS Flask API', [
                'response' => $response,
            ]);
            throw new \RuntimeException('Invalid JSON received from FSRS Flask API.');
        }

        $this->logger->info('Successfully fetched FSRS initial parameters from Flask API', [
            'result' => $result,
        ]);

        return $result;
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
     * Valide les résultats retournés par FSRS.
     *
     * @param array $result
     * @throws \RuntimeException Si les résultats sont invalides.
     */
    private function validateFSRSResult(array $result): void
    {
        $requiredKeys = ['stability', 'difficulty', 'last_review', 'due', 'state', 'step'];
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $result)) {
                $this->logger->error('Missing key in FSRS result', ['key' => $key, 'result' => $result]);
                throw new \RuntimeException(sprintf('Missing key "%s" in FSRS result.', $key));
            }
        }
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
}
