<?php

namespace App\Controller;

use App\Entity\Vibe;
use App\Repository\VibeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AiController extends AbstractController
{
    public function __construct(
        private string $ollamaUrl,
        private string $ollamaModel,
    ) {}

    #[Route('/api/ai/chat', name: 'api_ai_chat', methods: ['POST'])]
    public function chat(Request $request, VibeRepository $vibeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['messages']) || !is_array($data['messages'])) {
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->getUser();

        $systemPrompt = <<<'PROMPT'
Tu es Noctys, un assistant IA de l'application Hoomy qui gère l'ambiance d'une maison connectée.
Ton rôle est d'analyser l'état émotionnel de l'utilisateur et de recommander une ambiance.

Les critères sont :
- mood : humeur générale de 0 (très mauvaise humeur) à 10 (très bonne humeur)
- tone : énergie/tonalité de 0 (très calme, fatigué) à 10 (très énergique, festif)
- stress : niveau de stress de 0 (détendu) à 10 (très stressé)

Si tu as assez d'informations pour évaluer ces trois critères, réponds UNIQUEMENT en JSON valide :
{"ready": true, "mood": <0-10>, "tone": <0-10>, "stress": <0-10>, "explanation": "<courte phrase expliquant ton choix>"}

Si tu as besoin de plus d'informations, réponds UNIQUEMENT en JSON valide :
{"ready": false, "message": "<ta question en français>"}

Ne réponds RIEN d'autre que ce JSON. Pas de texte avant, pas de texte après, pas de markdown.
PROMPT;

        $ollamaMessages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $data['messages']
        );

        $rawContent = $this->callOllama($ollamaMessages);

        if ($rawContent === null) {
            return new JsonResponse([
                'ready' => false,
                'message' => "Ollama n'est pas disponible. Assurez-vous qu'il est bien lancé (`ollama serve`)."
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Strip potential markdown code fences
        $cleaned = trim(preg_replace('/^```(?:json)?\s*/i', '', preg_replace('/```\s*$/', '', trim($rawContent))));

        $parsed = json_decode($cleaned, true);

        if (!is_array($parsed) || !isset($parsed['ready'])) {
            return new JsonResponse([
                'ready'   => false,
                'message' => $cleaned ?: "Je n'ai pas pu analyser votre demande. Pouvez-vous reformuler ?",
            ]);
        }

        if ($parsed['ready'] === false) {
            return new JsonResponse([
                'ready'   => false,
                'message' => $parsed['message'] ?? "Pouvez-vous me donner plus de détails ?",
            ]);
        }

        $mood   = max(0, min(10, (int) ($parsed['mood']   ?? 5)));
        $tone   = max(0, min(10, (int) ($parsed['tone']   ?? 5)));
        $stress = max(0, min(10, (int) ($parsed['stress'] ?? 5)));

        $vibes = $vibeRepository->getAllForUser($user->getId());

        $results = [];
        foreach ($vibes as $vibe) {
            $c = $vibe->getCriteria();
            if (!$c) continue;
            $distance = sqrt(
                pow($c->getMood()   - $mood,   2) +
                pow($c->getTone()   - $tone,   2) +
                pow($c->getStress() - $stress, 2)
            );
            $results[] = ['vibe' => $vibe, 'distance' => $distance];
        }

        usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);

        $formattedVibes = array_map(function ($r) {
            $vibe     = $r['vibe'];
            $settings = [];
            foreach ($vibe->getSettings() as $setting) {
                $device     = $setting->getDevice();
                $settings[] = [
                    'value'         => $setting->getValue(),
                    'deviceLabel'   => $device->getLabel(),
                    'deviceRef'     => $device->getReference(),
                    'deviceAddress' => $device->getAddress(),
                    'featureLabel'  => $setting->getFeature()->getLabel(),
                ];
            }
            return [
                'id'       => $vibe->getId(),
                'label'    => $vibe->getLabel(),
                'score'    => round($r['distance'], 2),
                'icon'     => $vibe->getIcon()?->getImagePath(),
                'settings' => $settings,
            ];
        }, array_slice($results, 0, 3));

        return new JsonResponse([
            'ready'       => true,
            'criteria'    => ['mood' => $mood, 'tone' => $tone, 'stress' => $stress],
            'explanation' => $parsed['explanation'] ?? '',
            'vibes'       => $formattedVibes,
        ]);
    }

    private function callOllama(array $messages): ?string
    {
        $payload = json_encode([
            'model'    => $this->ollamaModel,
            'messages' => $messages,
            'stream'   => false,
        ]);

        $ch = curl_init($this->ollamaUrl . '/api/chat');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error || $response === false) {
            return null;
        }

        $decoded = json_decode($response, true);
        return $decoded['message']['content'] ?? null;
    }
}
