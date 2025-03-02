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
        $this->apiKey = $_ENV['OPENROUTER_API_KEY'];
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
                echo "⏳ En attente de la réponse de l'IA...\n";
                flush();

                $data = $form->getData();
                $title = $data['title'];
                $subject = $data['subject'];
                $context = $data['context'] ?? '';

                // 📝 Création du prompt pour l'IA
                $prompt = "Génère un paquet de flashcards sur '$subject'. Contexte : '$context'. 
                Réponds uniquement avec un JSON sous cette forme : 
                [{\"recto\": \"...\", \"verso\": \"...\"}].";

                try {
                    // 🔹 Envoi de la requête à l'API OpenRouter
                    $response = $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'model' => 'google/gemini-2.0-pro-exp-02-05:free',
                            'messages' => [
                                ["role" => "system", "content" => "Tu es un assistant qui génère des flashcards en JSON."],
                                ["role" => "user", "content" => $prompt]
                            ],
                            'temperature' => 0.7,
                            'max_tokens' => 3000
                        ],
                    ]);

                    // ✅ Vérification si la requête est réussie
                    if ($response->getStatusCode() !== 200) {
                        echo "❌ Erreur : L'API OpenRouter a retourné une erreur (HTTP " . $response->getStatusCode() . ")\n";
                        flush();
                        return;
                    }

                    // 🔍 Récupération de la réponse de l'IA
                    $result = json_decode($response->getContent(), true);

                    if (!isset($result['choices'][0]['message']['content'])) {
                        echo "❌ Erreur : L'IA n'a pas retourné de contenu valide.\n";
                        flush();
                        return;
                    }

                    echo "✅ Réponse reçue !\n";
                    flush();

                    $aiResponse = $result['choices'][0]['message']['content']; // Texte brut

                    // ✅ Nettoyage et conversion de la réponse en JSON
                    $aiResponse = trim($aiResponse); // Suppression des espaces inutiles
                    $aiResponse = preg_replace('/^```json|```$/', '', $aiResponse); // Suppression des balises Markdown JSON
                    $flashcardsArray = json_decode($aiResponse, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        echo "❌ Erreur de conversion JSON : " . json_last_error_msg() . "\n";
                        flush();
                        return;
                    }

                    // ✅ Création du deck
                    $deck = $this->deckController->createDeckEntity($title);
                    echo "✅ Deck créé avec succès !\n";
                    flush();

                    // ✅ Création des flashcards
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

                    echo "🎉 Toutes les flashcards ont été générées et enregistrées !\n";
                    flush();

                } catch (\Exception $e) {
                    echo "❌ Erreur lors de l'appel à l'IA : " . $e->getMessage() . "\n";
                    flush();
                }
            });
        }

        return $this->render('ai/index.html.twig', [
            'form' => $form->createView(),
            'aiResponse' => $aiResponse, // Affichage brut de la réponse de l'IA
        ]);
    }
}
