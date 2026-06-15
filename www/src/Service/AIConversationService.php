<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIConversationService
{
    private HttpClientInterface $httpClient;
    private string $ollamaUrl;
    
    public function __construct(
        HttpClientInterface $httpClient,
        string $ollamaUrl = 'http://localhost:11434',
        string $ollamaModel = 'llama3.2'
    ) {
        $this->httpClient = $httpClient;
        $this->ollamaUrl = $ollamaUrl;
        $this->ollamaModel = $ollamaModel;
    }

    /**
     * Analyse une conversation et retourne les critères d'humeur
     * 
     * @param array $messages Format: [['role' => 'user', 'content' => '...'], ...]
     * @return array ['mood' => int, 'stress' => int, 'tone' => int, 'explanation' => string]
     */
    public function analyzeMood(array $messages): array
    {
        $systemPrompt = $this->getSystemPrompt();
        
        // Prépare les messages pour Ollama
        $ollamaMessages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];
        
        foreach ($messages as $msg) {
            $ollamaMessages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['content']
            ];
        }
        
        try {
            $response = $this->httpClient->request('POST', $this->ollamaUrl . '/api/chat', [
                'json' => [
                    'model' => $this->ollamaModel,
                    'messages' => $ollamaMessages,
                    'stream' => false,
                    'format' => 'json', // Force la réponse en JSON
                ],
                'timeout' => 30, // Timeout de 30 secondes
            ]);

            $data = $response->toArray();
            $content = $data['message']['content'];
            
            // Parse la réponse JSON
            $result = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Ollama');
            }
            
            return $result;
            
        } catch (\Exception $e) {
            throw new \Exception('Erreur Ollama: ' . $e->getMessage());
        }
    }

    private function getSystemPrompt(): string
    {
        return <<<PROMPT
Tu es un assistant domotique intelligent qui analyse l'état émotionnel des utilisateurs français.

Après avoir discuté avec l'utilisateur, tu dois évaluer 3 critères sur une échelle de 0 à 100 :

1. **MOOD** (Humeur générale) :
   - 0-33 : Humeur négative, triste, déprimé, anxieux
   - 34-66 : Humeur neutre, calme, normal
   - 67-100 : Humeur positive, joyeux, enthousiaste, excité

2. **STRESS** (Niveau de stress) :
   - 0-33 : Très détendu, relaxé, serein
   - 34-66 : Stress modéré, niveau normal
   - 67-100 : Très stressé, tendu, anxieux, débordé

3. **TONE** (Niveau d'énergie) :
   - 0-33 : Faible énergie, fatigué, envie de calme
   - 34-66 : Énergie modérée, normal
   - 67-100 : Haute énergie, dynamique, envie d'action

RÈGLES IMPORTANTES :
- Pose 2-3 questions courtes pour bien comprendre l'état de l'utilisateur
- Sois empathique et naturel dans la conversation
- Parle en français
- Une fois que tu as assez d'informations, réponds UNIQUEMENT avec un JSON valide (sans markdown, sans ```json)

Format de réponse finale (après avoir posé tes questions) :
{
  "ready": true,
  "mood": 75,
  "stress": 40,
  "tone": 60,
  "explanation": "L'utilisateur semble joyeux et énergique, avec un stress modéré dû au travail"
}

Si tu as besoin de plus d'informations, réponds :
{
  "ready": false,
  "message": "Comment s'est passée ta journée ?"
}

TRÈS IMPORTANT : Réponds UNIQUEMENT en JSON valide, sans texte avant ou après.
PROMPT;
    }

    /**
     * Trouve les 3 vibes les plus adaptés aux critères
     */
    public function matchVibes(array $criteria, array $vibes): array
    {
        $scores = [];
        
        foreach ($vibes as $vibe) {
            if (!$vibe->getCriteria()) {
                continue;
            }
            
            $vibeCriteria = $vibe->getCriteria();
            
            // Guard : si l'un des critères est null, on skip
            if ($vibeCriteria->getMood() === null 
                || $vibeCriteria->getStress() === null 
                || $vibeCriteria->getTone() === null) {
                continue;
            }
            
            $distance = sqrt(
                pow($criteria['mood'] - $vibeCriteria->getMood(), 2) +
                pow($criteria['stress'] - $vibeCriteria->getStress(), 2) +
                pow($criteria['tone'] - $vibeCriteria->getTone(), 2)
            );
            
            $score = max(0, 100 - ($distance / 1.732));
            
            $scores[] = [
                'vibe' => $vibe,
                'score' => $score,
            ];
        }
        
        usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice($scores, 0, 3);
    }

    /**
     * Vérifie si Ollama est disponible
     */
    public function checkOllamaStatus(): bool
    {
        try {
            $response = $this->httpClient->request('GET', $this->ollamaUrl . '/api/tags', [
                'timeout' => 5,
            ]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Liste les modèles disponibles
     */
    public function listModels(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->ollamaUrl . '/api/tags');
            $data = $response->toArray();
            return $data['models'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}