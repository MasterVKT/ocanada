<?php
declare(strict_types=1);
?>
<button id="chatbot-trigger"
        type="button"
        class="chatbot-trigger btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
        aria-expanded="false"
        aria-controls="chatbot-panel"
        aria-label="Ouvrir l assistant RH">
    <i class="bi bi-robot fs-4"></i>
</button>

<div id="chatbot-panel" class="chatbot-panel d-none" aria-hidden="true">
    <div class="chatbot-header d-flex align-items-center justify-content-between px-3 py-2">
        <div>
            <div class="fw-semibold">Assistant RH — Ô Canada</div>
            <div class="small chatbot-subtitle">Posez vos questions RH</div>
        </div>
        <button type="button" class="btn btn-sm btn-light" id="chatbot-close" aria-label="Fermer le chatbot">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div id="chatbot-messages" class="chatbot-messages p-3">
        <div class="chatbot-message chatbot-message-bot">
            Bonjour ! Je suis votre assistant RH. Posez-moi vos questions sur vos congés, vos présences ou les règles de l'entreprise.
        </div>
    </div>
    <form id="chatbot-form" class="chatbot-input border-top p-2 d-flex gap-2">
        <input type="text" name="message" id="chatbot-input" class="form-control" placeholder="Écrire un message..." autocomplete="off">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-send"></i>
        </button>
    </form>
</div>

