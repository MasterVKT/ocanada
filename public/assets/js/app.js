(() => {
    'use strict';

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    window.securePost = async (url, body = {}) => {
        const token = csrfMeta ? csrfMeta.content : '';
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(body),
        });

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }

        return res.json();
    };

    window.showToast = (message, type = 'info', duration = 4000) => {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const div = document.createElement('div');
        div.className = `toast align-items-center text-white bg-${type} border-0 show`;
        div.setAttribute('role', 'alert');
        div.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        container.appendChild(div);
        setTimeout(() => div.remove(), duration);
    };

    const chatbotTrigger = document.getElementById('chatbot-trigger');
    const chatbotPanel = document.getElementById('chatbot-panel');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotForm = document.getElementById('chatbot-form');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotMessages = document.getElementById('chatbot-messages');

    const toggleChatbot = (visible) => {
        if (!chatbotPanel) return;
        chatbotPanel.classList.toggle('d-none', !visible);
        chatbotPanel.setAttribute('aria-hidden', visible ? 'false' : 'true');
        if (chatbotTrigger) {
            chatbotTrigger.setAttribute('aria-expanded', visible ? 'true' : 'false');
        }
        if (visible && chatbotInput) {
            chatbotInput.focus();
        }
    };

    if (chatbotTrigger) {
        chatbotTrigger.addEventListener('click', () => toggleChatbot(true));
    }

    if (chatbotClose) {
        chatbotClose.addEventListener('click', () => toggleChatbot(false));
    }

    if (chatbotForm && chatbotMessages && chatbotInput) {
        chatbotForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const text = chatbotInput.value.trim();
            if (!text) return;

            const userDiv = document.createElement('div');
            userDiv.className = 'chatbot-message chatbot-message-user';
            userDiv.textContent = text;
            chatbotMessages.appendChild(userDiv);
            requestAnimationFrame(() => { chatbotMessages.scrollTop = chatbotMessages.scrollHeight; });

            chatbotInput.value = '';

            const typingDiv = document.createElement('div');
            typingDiv.className = 'chatbot-message chatbot-message-bot text-muted';
            typingDiv.textContent = '...';
            chatbotMessages.appendChild(typingDiv);
            requestAnimationFrame(() => { chatbotMessages.scrollTop = chatbotMessages.scrollHeight; });

            try {
                const data = await window.securePost('/ia/chatbot', { message: text });

                typingDiv.remove();

                const botDiv = document.createElement('div');
                botDiv.className = 'chatbot-message chatbot-message-bot';
                botDiv.textContent = data.reply || 'Aucune reponse.';
                chatbotMessages.appendChild(botDiv);
                requestAnimationFrame(() => { chatbotMessages.scrollTop = chatbotMessages.scrollHeight; });
            } catch (error) {
                typingDiv.remove();

                const botDiv = document.createElement('div');
                botDiv.className = 'chatbot-message chatbot-message-bot text-danger';
                botDiv.textContent = 'Le service est indisponible pour le moment.';
                chatbotMessages.appendChild(botDiv);
                requestAnimationFrame(() => { chatbotMessages.scrollTop = chatbotMessages.scrollHeight; });
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && chatbotPanel && !chatbotPanel.classList.contains('d-none')) {
            toggleChatbot(false);
        }
    });
})();

