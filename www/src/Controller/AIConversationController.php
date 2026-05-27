<?php

namespace App\Controller;

use App\Service\AIConversationService;
use App\Repository\VibeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai', name: 'api_ai_')]
class AIConversationController extends AbstractController
{
    public function __construct(
        private AIConversationService $aiService,
        private VibeRepository $vibeRepository,
        private EntityManagerInterface $em
    ) {}

    /**
     * Vérifie le status d'Ollama
     */
    #[Route('/status', name: 'status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        $isOnline = $this->aiService->checkOllamaStatus();
        $models = $this->aiService->listModels();
        
        return $this->json([
            'ollama_online' => $isOnline,
            'models' => $models,
            'message' => $isOnline 
                ? 'Ollama est opérationnel' 
                : 'Ollama n\'est pas disponible. Assurez-vous qu\'il est lancé (ollama serve)'
        ]);
    }

    /**
     * Endpoint pour la conversation avec l'IA
     */
    #[Route('/chat', name: 'chat', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $messages = $data['messages'] ?? [];
        
        if (empty($messages)) {
            return $this->json(['error' => 'Messages required'], 400);
        }

        try {
            $result = $this->aiService->analyzeMood($messages);
            
            // Si l'IA a besoin de plus d'infos
            if (!($result['ready'] ?? false)) {
                return $this->json([
                    'ready' => false,
                    'message' => $result['message']
                ]);
            }
            
            // Récupère tous les vibes du profil
            $profile = $this->getUser(); // Adaptez selon votre auth
            $vibes = $this->vibeRepository->findBy(['profile' => $profile]);
            
            // Trouve les 3 meilleurs vibes
            $matches = $this->aiService->matchVibes([
                'mood' => $result['mood'],
                'stress' => $result['stress'],
                'tone' => $result['tone'],
            ], $vibes);
            
            return $this->json([
                'ready' => true,
                'criteria' => [
                    'mood' => $result['mood'],
                    'stress' => $result['stress'],
                    'tone' => $result['tone'],
                ],
                'explanation' => $result['explanation'],
                'vibes' => array_map(function($match) {
                    return [
                        'id' => $match['vibe']->getId(),
                        'label' => $match['vibe']->getLabel(),
                        'score' => round($match['score'], 2),
                        'icon' => $match['vibe']->getIcon()?->getImagePath(),
                    ];
                }, $matches)
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Activer un vibe suggéré par l'IA
     */
    #[Route('/activate-vibe/{id}', name: 'activate_vibe', methods: ['POST'])]
    public function activateVibe(int $id): JsonResponse
    {
        $profile = $this->getUser();
        $vibe = $this->vibeRepository->find($id);
        
        if (!$vibe || $vibe->getProfile() !== $profile) {
            return $this->json(['error' => 'Vibe not found'], 404);
        }
        
        // Active le vibe (votre logique existante)
        // TODO: Implémenter l'activation du vibe
        
        return $this->json([
            'success' => true,
            'message' => "Vibe '{$vibe->getLabel()}' activé"
        ]);
    }
}