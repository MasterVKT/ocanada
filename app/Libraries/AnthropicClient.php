<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\ConfigSystemeModel;

/**
 * Client IA (Gemini) conservant le nom historique pour compatibilite.
 */
class AnthropicClient
{
    protected string $apiKey;
    protected string $model = 'gemini-2.5-flash';
    protected int $maxTokens = 240;
    protected int $cacheTtl = 21600; // 6h
    protected RateLimiter $rateLimiter;
    protected ConfigSystemeModel $configModel;

    public function __construct()
    {
        $this->configModel = model(ConfigSystemeModel::class);
        $this->apiKey      = $this->resolveApiKey();
        $this->model       = trim((string) ($this->configModel->get('gemini_model', 'gemini-2.5-flash') ?? 'gemini-2.5-flash'));
        $this->rateLimiter = new RateLimiter();
    }

    /**
     * Envoie une requete a Gemini avec cache pour limiter les appels API.
     */
    public function sendMessage(string $message, array $context = []): ?string
    {
        $message = trim($message);
        if ($message === '') {
            return null;
        }

        $cached = $this->getCachedResponse($message, $context);
        if ($cached !== null) {
            return $cached;
        }

        // Limite globale defensive en plus de celle du controleur.
        if (!$this->rateLimiter->canMakeRequest()) {
            return 'Le service IA est temporairement limite. Reessayez dans quelques minutes.';
        }

        if ($this->apiKey === '') {
            return $this->fallbackReply($message);
        }

        $prompt = $this->buildPrompt($message, $context);
        $reply = $this->callGeminiApi($prompt, $this->maxTokens);

        if ($reply === null || $reply === '') {
            return $this->fallbackReply($message);
        }

        $this->saveCachedResponse($message, $context, $reply);

        return $reply;
    }

    protected function callGeminiApi(string $prompt, int $maxOutputTokens): ?string
    {
        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            rawurlencode($this->model),
            rawurlencode($this->apiKey)
        );

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topP' => 0.8,
                'maxOutputTokens' => max(64, min(800, $maxOutputTokens)),
            ],
        ];

        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!is_string($rawResponse) || $rawResponse === '' || $httpCode >= 400) {
            if ($curlError !== '') {
                log_message('warning', 'Gemini cURL error: ' . $curlError);
            }

            if (is_string($rawResponse) && $rawResponse !== '') {
                log_message('warning', 'Gemini HTTP ' . $httpCode . ' response: ' . substr($rawResponse, 0, 500));
            }

            return null;
        }

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            return null;
        }

        $parts = $decoded['candidates'][0]['content']['parts'] ?? null;
        if (!is_array($parts)) {
            return null;
        }

        $texts = [];
        foreach ($parts as $part) {
            $text = trim((string) ($part['text'] ?? ''));
            if ($text !== '') {
                $texts[] = $text;
            }
        }

        return $texts === [] ? null : implode("\n", $texts);
    }

    protected function buildPrompt(string $message, array $context): string
    {
        $role = (string) ($context['role'] ?? '');
        $nom = (string) ($context['nom'] ?? '');
        $normalized = mb_substr($message, 0, 1200);

        $system = 'Tu es un assistant RH interne de O Canada. Reponds en francais simple, en 3-6 phrases max. '
            . 'Si la question sort du perimetre RH interne (conges, presences, planning, procedures internes), indique poliment la limite.';

        return trim($system . "\n"
            . 'Contexte utilisateur: role=' . $role . ', nom=' . $nom . "\n"
            . 'Question: ' . $normalized);
    }

    protected function resolveApiKey(): string
    {
        $dbGemini = trim((string) ($this->configModel->get('gemini_api_key', '') ?? ''));
        if ($dbGemini !== '') {
            return $dbGemini;
        }

        // Compatibilite: reutilise l'ancien champ de config si deja renseigne.
        $legacyDb = trim((string) ($this->configModel->get('anthropic_api_key', '') ?? ''));
        if ($legacyDb !== '') {
            return $legacyDb;
        }

        $envGemini = trim((string) (getenv('GEMINI_API_KEY') ?: ''));
        if ($envGemini !== '') {
            return $envGemini;
        }

        return trim((string) (getenv('GOOGLE_API_KEY') ?: ''));
    }

    protected function cacheKey(string $message, array $context): string
    {
        $fingerprint = strtolower(trim($message)) . '|' . json_encode($context, JSON_UNESCAPED_UNICODE);

        return 'ai_gemini_' . md5($fingerprint);
    }

    protected function getCachedResponse(string $message, array $context): ?string
    {
        $cache = \Config\Services::cache();
        $cached = $cache->get($this->cacheKey($message, $context));

        return is_string($cached) && $cached !== '' ? $cached : null;
    }

    protected function saveCachedResponse(string $message, array $context, string $reply): void
    {
        $cache = \Config\Services::cache();
        $cache->save($this->cacheKey($message, $context), $reply, $this->cacheTtl);
    }

    protected function fallbackReply(string $message): string
    {
        $text = mb_strtolower($message);
        if (str_contains($text, 'conge')) {
            return 'Pour un conge, precisez les dates, le type de conge et le motif. Je peux ensuite vous proposer un texte pret a envoyer.';
        }

        if (str_contains($text, 'retard') || str_contains($text, 'absence')) {
            return 'Les retards et absences sont relies au planning et aux pointages kiosque. Verifiez vos horaires puis consultez votre historique des presences.';
        }

        return 'Je peux vous aider sur les conges, presences et planning. Posez une question plus precise pour une reponse utile.';
    }

    /**
     * Génère un texte d'assistance pour congé
     */
    public function generateLeaveText(string $typeConge, string $motif): string
    {
        $prompt = "Redige une demande de conge professionnelle en francais. "
            . "Type de conge: {$typeConge}. Motif: {$motif}. "
            . 'Format attendu: objet, formule d appel, corps du texte, formule de politesse.';

        $originalMax = $this->maxTokens;
        $this->maxTokens = 420;
        $response = $this->sendMessage($prompt, ['feature' => 'assistant_conge']);
        $this->maxTokens = $originalMax;

        return $response ?: 'Erreur de generation';
    }

    /**
     * Répond à une question du chatbot RH
     */
    public function chatResponse(string $question, array $context): string
    {
        $prompt = "Question RH: {$question}";

        return $this->sendMessage($prompt, $context) ?: 'Desole, je ne peux pas repondre pour le moment.';
    }
}
