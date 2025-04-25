<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Form\AIType;
use App\Controller\DeckController;
use App\Controller\FlashcardController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIController extends AbstractController
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

    #[Route('/ai', name: 'ai_form', methods: ['GET', 'POST'])]
    public function generateFlashcards(Request $request): Response
    {
        $form = $this->createForm(AIType::class);
        $form->handleRequest($request);
        $aiResponse = null;

        if ($form->isSubmitted() && $form->isValid()) {
            return new StreamedResponse(function () use ($form) {
                ob_implicit_flush(1);
                echo "⏳ En attente de la réponse de l'IA (DeepSeek)...\n";
                flush();

                $data = $form->getData();
                $title = $data['title'];
                $promptUser = $data['prompt'];
                $resources = $data['resources'] ?? '';

                $prompt = "Generate a set of flashcards based on the following prompt: '$promptUser'. Additional resources: '$resources'. Respond only with a JSON array with this format: [{\"recto\": \"...\", \"verso\": \"...\", \"search_term\": \"...\"}]. 'search_term' must be a precise French Wikipedia page title that best matches the concept to find the best main image (e.g., 'Drapeau de la France', 'Tour Eiffel', 'Lion (animal)'). Never include links or HTML, only pure text.";

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
                                ["role" => "user", "content" => $prompt]
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 6000,
                            'stream' => false
                        ],
                    ]);

                    if ($response->getStatusCode() !== 200) {
                        echo "❌ Erreur : L'API DeepSeek a retourné une erreur (HTTP " . $response->getStatusCode() . ")\n";
                        flush();
                        return;
                    }

                    $contentRaw = $response->getContent();
                    $result = json_decode($contentRaw, true);

                    $content = $result['choices'][0]['message']['content']
                        ?? $result['choices'][0]['content']
                        ?? null;

                    if (!$content) {
                        echo "❌ Erreur : L'IA n'a pas retourné de contenu utilisable.\n";
                        flush();
                        return;
                    }

                    echo "✅ Réponse reçue !\n";
                    flush();

                    $aiResponse = trim($content);
                    $aiResponse = preg_replace('/^```json|```$/', '', $aiResponse);
                    $flashcardsArray = json_decode($aiResponse, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo "❌ Erreur de conversion JSON : " . json_last_error_msg() . "\n";
                        flush();
                        return;
                    }

                    $deck = $this->deckController->createDeckEntity($title);
                    echo "✅ Deck créé avec succès !\n";
                    flush();

                    foreach ($flashcardsArray as $flashcardData) {
                        if (isset($flashcardData['recto'], $flashcardData['verso'])) {
                            $searchTerm = $flashcardData['search_term'] ?? $flashcardData['recto'];
                            $imageUrl = $this->fetchImageFromWikipedia($searchTerm);


                            $verso = $flashcardData['verso'];
                            if ($imageUrl) {
                                $verso .= '<br/><img src="' . $imageUrl . '" alt="' . htmlspecialchars($flashcardData['recto']) . '" style="max-width:100%; height:auto;" />';
                            }

                            $this->flashcardController->createFlashcard(
                                $deck,
                                $flashcardData['recto'],
                                $verso
                            );

                            echo "✅ Flashcard ajoutée : " . $flashcardData['recto'] . "\n";
                            flush();
                        }
                    }


                    $redirectUrl = '/deck/' . $deck->getId() . '/flashcards';
                    echo "🎉 Toutes les flashcards ont été générées et enregistrées ! Redirection dans un instant...\n";
                    echo "<script>setTimeout(() => { window.location.href = '$redirectUrl'; }, 2000);</script>\n";
                    flush();

                } catch (\Exception $e) {
                    echo "❌ Erreur lors de l'appel à l'IA : " . $e->getMessage() . "\n";
                    flush();
                }
            });
        }

        return $this->render('ai/index.html.twig', [
            'form' => $form->createView(),
            'aiResponse' => $aiResponse,
        ]);
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
                    echo "📷 Image Wikipédia trouvée pour '$term' : " . $page['original']['source'] . "\n";
                    flush();
                    return $page['original']['source'];
                }
            }
        } catch (\Exception $e) {
            echo "⚠️ Erreur lors de la récupération Wikipédia pour '$term' : " . $e->getMessage() . "\n";
            flush();
        }

        return null;
    }
}
