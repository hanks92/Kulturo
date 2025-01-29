<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FSRSService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Initialise une flashcard en envoyant une requête à l'API Flask.
     */
    public function initializeCard(int $flashcardId): array
    {
        $response = $this->client->request('POST', 'http://localhost:5000/initialize_card', [
            'json' => [
                'id' => $flashcardId,
            ],
        ]);

        return $response->toArray();
    }

    /**
     * Met à jour une carte via l'API Flask en fonction de la révision utilisateur.
     */
    public function reviewCard(array $cardData, int $rating): ?array
    {
        try {
            $response = $this->client->request('POST', 'http://localhost:5000/review', [
                'json' => [
                    'card' => $cardData,
                    'rating' => $rating,
                    'review_datetime' => (new \DateTime())->format(DATE_ISO8601),
                ],
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            // Gestion des erreurs (log optionnel ou retour null)
            return null;
        }
    }
}
