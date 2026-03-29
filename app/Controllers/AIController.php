<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\AnthropicClient;
use App\Libraries\RateLimiter;
use CodeIgniter\HTTP\ResponseInterface;

class AIController extends BaseController
{
    protected AnthropicClient $anthropic;
    protected RateLimiter $rateLimiter;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->anthropic = new AnthropicClient();
        $this->rateLimiter = new RateLimiter();
    }

    public function assistantConge(): ResponseInterface
    {
        $role = (string) ($this->currentUser['role'] ?? '');
        if ($role !== 'employe') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Acces refuse.',
            ], 403);
        }

        $payload = $this->request->getJSON(true);
        $userInput = trim((string) ($payload['user_input'] ?? $this->request->getPost('user_input') ?? ''));
        $typeConge = trim((string) ($payload['type_conge'] ?? $this->request->getPost('type_conge') ?? 'autre'));

        if ($userInput === '' || strlen($userInput) < 5) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Veuillez fournir un contexte plus detaille.',
            ], 422);
        }

        $userId = (int) ($this->currentUser['user_id'] ?? 0);
        if (! $this->rateLimiter->canMakeRequestFor('assistant-conge:' . $userId, 3, 3600)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Limite atteinte (3 demandes/heure).',
            ], 429);
        }

        try {
            $suggestion = $this->anthropic->generateLeaveText($typeConge, $userInput);

            return $this->jsonResponse([
                'success' => true,
                'suggestion' => $suggestion,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Assistant conge error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Service indisponible temporairement.',
            ], 500);
        }
    }

    public function chatbot(): ResponseInterface
    {
        $payload = $this->request->getJSON(true);
        $message = trim((string) ($payload['message'] ?? $this->request->getPost('message') ?? ''));

        if ($message === '') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Message vide.',
            ], 422);
        }

        $userId = (int) ($this->currentUser['user_id'] ?? 0);
        if (! $this->rateLimiter->canMakeRequestFor('chatbot:' . $userId, 20, 3600)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Limite atteinte (20 messages/heure).',
            ], 429);
        }

        $context = [
            'role' => (string) ($this->currentUser['role'] ?? ''),
            'nom' => (string) ($this->currentUser['nom_complet'] ?? ''),
        ];

        try {
            $reply = $this->anthropic->chatResponse($message, $context);

            return $this->jsonResponse([
                'success' => true,
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Chatbot error: ' . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Service indisponible temporairement.',
            ], 500);
        }
    }
}
