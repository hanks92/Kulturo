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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\User; // <-- Pour aider Intelephense

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
        /** @var User $user */
        $user = $this->getUser();

        if (!$user || !$user->isPremium()) {
            $this->addFlash('error', 'Cette fonctionnalité est réservée aux membres Premium 🚀');
            return $this->redirectToRoute('app_tarifs');
        }

        $form = $this->createForm(AIType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $request->getSession()->set('ai_generation_data', $form->getData());
            return $this->redirectToRoute('ai_loading_page');
        }

        return $this->render('ai/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ai/loading', name: 'ai_loading_page')]
    public function loading(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user || !$user->isPremium()) {
            $this->addFlash('error', 'Cette fonctionnalité est réservée aux membres Premium 🚀');
            return $this->redirectToRoute('app_tarifs');
        }

        return $this->render('ai/loading.html.twig');
    }

    #[Route('/ai/generate/backend', name: 'ai_generate_backend')]
    public function generateFlashcardsBackend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user || !$user->isPremium()) {
            return new JsonResponse([
                'error' => 'Accès réservé aux membres Premium.',
                'redirect' => $this->generateUrl('app_tarifs')
            ], 403);
        }

        $data = $request->getSession()->get('ai_generation_data');

        if (!$data) {
            return new JsonResponse(['error' => 'Aucune donnée trouvée en session.'], 400);
        }

        try {
            $title = $data['title'];
            $promptUser = $data['prompt'];
            $resources = $data['resources'] ?? '';

            $prompt = "Generate a set of flashcards based on the following prompt: '$promptUser'. Additional resources: '$resources'. Respond only with a JSON array formatted like: [{\"recto\": \"...\", \"verso\": \"...\", \"search_term\": \"...\"}]. 'search_term' must be a precise French Wikipedia page title that best matches the concept to find the best main image (e.g., 'Drapeau de la France', 'Tour Eiffel', 'Lion (animal)'). Never include links, HTML or images directly.";

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
                return new JsonResponse(['error' => 'Erreur DeepSeek.'], 500);
            }

            $contentRaw = $response->getContent();
            $result = json_decode($contentRaw, true);

            $content = $result['choices'][0]['message']['content']
                ?? $result['choices'][0]['content']
                ?? null;

            if (!$content) {
                return new JsonResponse(['error' => 'Contenu IA invalide.'], 500);
            }

            $aiResponse = trim($content);
            $aiResponse = preg_replace('/^```json|```$/', '', $aiResponse);
            $flashcardsArray = json_decode($aiResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Erreur de décodage JSON IA.'], 500);
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

                    $this->flashcardController->createFlashcard(
                        $deck,
                        $flashcardData['recto'],
                        $verso
                    );
                }
            }

            $redirectUrl = $this->generateUrl('flashcard_list', ['id' => $deck->getId()]);

            return new JsonResponse(['redirectUrl' => $redirectUrl]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur de génération : ' . $e->getMessage()], 500);
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
            // Pas d'image trouvée ou erreur API, on ignore proprement
        }

        return null;
    }
}
