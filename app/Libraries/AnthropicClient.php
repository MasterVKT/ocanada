<?php
declare(strict_types=1);

namespace App\Libraries;

/**
 * Client pour l'API Anthropic (Claude)
 */
class AnthropicClient
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.anthropic.com/v1/messages';
    protected int $maxTokens = 1000;
    protected RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->apiKey      = getenv('ANTHROPIC_API_KEY') ?: '';
        $this->rateLimiter = new RateLimiter();
    }

    /**
     * Envoie une requête à Claude
     */
    public function sendMessage(string $message, array $context = []): ?string
    {
        if (!$this->rateLimiter->canMakeRequest()) {
            throw new \Exception('Trop de requêtes, veuillez réessayer plus tard.');
        }

        // TODO: Implémenter l'appel API réel
        // Pour l'instant, retourner une réponse mock
        return 'Réponse simulée de Claude pour: ' . $message;
    }

    /**
     * Génère un texte d'assistance pour congé
     */
    public function generateLeaveText(string $typeConge, string $motif): string
    {
        $prompt = "Rédigez un courrier de demande de congé pour: {$typeConge}. Motif: {$motif}";

        return $this->sendMessage($prompt) ?: 'Erreur de génération';
    }

    /**
     * Répond à une question du chatbot RH
     */
    public function chatResponse(string $question, array $context): string
    {
        $contextStr = json_encode($context);
        $prompt     = "Question RH: {$question}. Contexte: {$contextStr}";

        return $this->sendMessage($prompt) ?: 'Désolé, je ne peux pas répondre pour le moment.';
    }
}