<?php

namespace App\Controller\Api;

use App\Controller\DeckController;
use App\Controller\FlashcardController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/ai', name: 'api_ai_')]
class AiGenerationController extends AbstractController
{
    private HttpClientInterface $httpClient;
    private EntityManagerInterface $entityManager;
    private DeckController $deckController;
    private FlashcardController $flashcardController;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        DeckController $deckController,
        FlashcardController $flashcardController
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->deckController = $deckController;
        $this->flashcardController = $flashcardController;
        $this->apiKey = $_ENV['DEEPSEEK_API_KEY'];

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Clé API DeepSeek manquante dans .env');
        }
    }

    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user || !$user->isPremium()) {
            return $this->json(['error' => 'Accès réservé aux membres Premium.'], 403);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['title'], $data['prompt'])) {
            return $this->json(['error' => 'Paramètres manquants.'], 400);
        }

        $title = $data['title'];
        $promptUser = $data['prompt'];
        $resources = $data['resources'] ?? '';

        $prompt = "Generate a set of flashcards based on the following prompt: '$promptUser'. Additional resources: '$resources'. Respond only with a JSON array formatted like: [{\"recto\": \"...\", \"verso\": \"...\", \"search_term\": \"...\"}]. 'search_term' must be a precise French Wikipedia page title that best matches the concept to find the best main image (e.g., 'Drapeau de la France', 'Tour Eiffel', 'Lion (animal)'). Never include links, HTML or images directly.";

        try {
            $response = $this->httpClient->request('POST', 'https://api.deepseek.com/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        ["role" => "system", "content" => "You are a helpful assistant that generates JSON-formatted flashcards."],
                        ["role" => "user", "content" => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 6000,
                    'stream' => false,
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return $this->json(['error' => 'Erreur DeepSeek.'], 500);
            }

            $contentRaw = $response->getContent();
            $result = json_decode($contentRaw, true);

            $content = $result['choices'][0]['message']['content']
                ?? $result['choices'][0]['content']
                ?? null;

            if (!$content) {
                return $this->json(['error' => 'Contenu IA invalide.'], 500);
            }

            $aiResponse = trim($content);
            $aiResponse = preg_replace('/^```json|```$/', '', $aiResponse);
            $flashcardsArray = json_decode($aiResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json(['error' => 'Erreur de décodage JSON IA.'], 500);
            }

            $deck = $this->deckController->createDeckEntity($title);

            foreach ($flashcardsArray as $flashcardData) {
                if (isset($flashcardData['recto'], $flashcardData['verso'])) {
                    $searchTerm = $flashcardData['search_term'] ?? $flashcardData['recto'];
                    $imageUrl = $this->fetchImageFromWikipedia($searchTerm);

                    $verso = $flashcardData['verso'];
                    if ($imageUrl) {
                        $verso .= '<br/><img src="' . $imageUrl . '" alt="' . htmlspecialchars($flashcardData['recto']) . '" style="max-width:100%; height:auto;" />';
                    }

                    $this->flashcardController->createFlashcard($deck, $flashcardData['recto'], $verso);
                }
            }

            return $this->json([
                'deck_id' => $deck->getId(),
                'deck_title' => $deck->getTitle(),
                'message' => 'Deck généré avec succès.'
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur de génération : ' . $e->getMessage()], 500);
        }
    }

    private function fetchImageFromWikipedia(string $term): ?string
    {
        try {
            $response = $this->httpClient->request('GET', 'https://fr.wikipedia.org/w/api.php', [
                'query' => [
                    'action' => 'query',
                    'format' => 'json',
                    'titles' => $term,
                    'redirects' => 1,
                    'prop' => 'pageimages',
                    'piprop' => 'original',
                ],
            ]);

            $data = $response->toArray();
            $pages = $data['query']['pages'] ?? [];

            foreach ($pages as $page) {
                if (isset($page['original']['source'])) {
                    return $page['original']['source'];
                }
            }
        } catch (\Exception $e) {
            // on ignore les erreurs silencieusement
        }

        return null;
    }
}
