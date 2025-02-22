<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;
use DateTime;
use DateTimeZone;

class FSRSService
{
    private HttpClientInterface $client;
    private string $flaskApiUrl;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, ?string $flaskApiUrl, LoggerInterface $logger)
    {
        // Correction de l'URL si elle commence par "tcp://"
        if ($flaskApiUrl && str_starts_with($flaskApiUrl, 'tcp://')) {
            $flaskApiUrl = str_replace('tcp://', 'http://', $flaskApiUrl);
        }

        $this->flaskApiUrl = $flaskApiUrl ?: 'http://127.0.0.1:5000';

        if (!preg_match('/^https?:\/\//', $this->flaskApiUrl)) {
            throw new \InvalidArgumentException("L'URL de l'API Flask est invalide : {$this->flaskApiUrl}. Elle doit commencer par http:// ou https://");
        }

        $this->client = $client;
        $this->logger = $logger;
        $this->logger->info("🌍 URL de l'API Flask utilisée : {$this->flaskApiUrl}");
    }

    /**
     * Initialise une flashcard en envoyant une requête à l'API Flask.
     */
    public function initializeCard(int $flashcardId): ?array
    {
        $url = "{$this->flaskApiUrl}/initialize_card";

        try {
            $this->logger->info('🟡 Envoi à FSRS (Initialisation)', ['id' => $flashcardId]);

            $response = $this->client->request('POST', $url, [
                'json' => ['id' => $flashcardId],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('FSRS API returned an error: ' . $response->getContent(false));
            }

            $result = $response->toArray();

            // Assurer que toutes les clés sont bien présentes, même si elles sont NULL
            $result['stability'] = $result['stability'] ?? null;
            $result['difficulty'] = $result['difficulty'] ?? null;
            $result['state'] = $result['state'] ?? 1;
            $result['step'] = $result['step'] ?? 0;
            $result['due'] = $result['due'] ?? (new DateTime())->format(DateTime::ISO8601);
            $result['last_review'] = null; // Initialisation

            $this->logger->info('🟢 Réponse FSRS (Initialisation)', $result);
            return $result;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('🔴 Erreur API Flask (Initialisation)', ['message' => $e->getMessage()]);
            return null;
        } catch (\Exception $e) {
            $this->logger->error('🔴 Erreur lors de l\'initialisation FSRS', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Met à jour une carte via l'API Flask en fonction de la révision utilisateur.
     */
    public function updateCard(array $cardData, int $rating): ?array
    {
        $url = "{$this->flaskApiUrl}/review";
        $reviewDateTime = (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ISO8601);

        try {
            $this->logger->info('🔵 Envoi à FSRS (Mise à jour)', [
                'card' => $cardData,
                'rating' => $rating,
                'review_datetime' => $reviewDateTime,
            ]);

            $response = $this->client->request('POST', $url, [
                'json' => [
                    'card' => $cardData,
                    'rating' => $rating,
                    'review_datetime' => $reviewDateTime,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('FSRS API returned an error: ' . $response->getContent(false));
            }

            $result = $response->toArray();
            if (!isset($result['card']) || !isset($result['review_log'])) {
                throw new \Exception('Réponse FSRS invalide : données manquantes.');
            }

            // Mise à jour des paramètres de la carte
            $updatedCard = $result['card'];
            $updatedCard['last_review'] = $reviewDateTime; // Mise à jour de la dernière révision

            $reviewLog = [
                'rating' => $result['review_log']['rating'] ?? $rating,
                'review_datetime' => $reviewDateTime,
                'review_duration' => $result['review_log']['review_duration'] ?? null,
            ];

            $this->logger->info('🟢 Réponse FSRS (Mise à jour)', [
                'updated_card' => $updatedCard,
                'review_log' => $reviewLog
            ]);

            return [
                'updated_card' => $updatedCard,
                'review_log' => $reviewLog,
            ];
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('🔴 Erreur API Flask (Mise à jour)', ['message' => $e->getMessage()]);
            return null;
        } catch (\Exception $e) {
            $this->logger->error('🔴 Erreur lors de la mise à jour FSRS', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
