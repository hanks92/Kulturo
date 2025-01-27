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

    public function initializeCard(int $flashcardId): array
    {
        $response = $this->client->request('POST', 'http://localhost:5000/initialize_card', [
            'json' => [
                'id' => $flashcardId,
            ],
        ]);

        return $response->toArray();
    }
}
