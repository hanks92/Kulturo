<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIClient
{
    private HttpClientInterface $httpClient;
    private string $aiUrl;

    public function __construct(HttpClientInterface $httpClient, string $aiUrl)
    {
        $this->httpClient = $httpClient;
        $this->aiUrl = $aiUrl;
    }

    public function generateFlashcards(string $subject, string $context, ?array $documents = []): array
    {
        // Préparer les données pour l'IA
        $data = [
            'subject' => $subject,
            'context' => $context,
            'documents' => $documents ?? []
        ];

        // Envoyer une requête POST à l'IA
        $response = $this->httpClient->request('POST', $this->aiUrl, [
            'json' => $data,
        ]);

        // Décoder la réponse JSON de l'IA
        return $response->toArray();
    }
}
