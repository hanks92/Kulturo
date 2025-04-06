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

                $prompt = "Génère un paquet de flashcards selon le prompt suivant : '$promptUser'. Ressources supplémentaires fournies par l'utilisateur : '$resources'. Réponds uniquement avec un JSON sous cette forme : [{\"recto\": \"...\", \"verso\": \"...\"}].";

                try {
                    $response = $this->httpClient->request('POST', 'https://api.deepseek.com/chat/completions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => 'deepseek-chat',
                            'messages' => [
                                ["role" => "system", "content" => "Tu es un assistant qui génère des flashcards en JSON."],
                                ["role" => "user", "content" => $prompt]
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 3000,
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
                            $this->flashcardController->createFlashcard(
                                $deck,
                                $flashcardData['recto'],
                                $flashcardData['verso']
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
}
