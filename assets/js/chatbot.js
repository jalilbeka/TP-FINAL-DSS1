/* ============================================================
   DSS University — AI Support Chatbot Widget
   ============================================================ */

const chatbot = (() => {
  const MAX_MESSAGE_LENGTH = 1000;
  const MAX_HISTORY_TURNS = 10;
  const COOLDOWN_MS = 3000;

  let isOpen = false;
  let isLoading = false;
  let hasWelcome = false;
  let cooldownUntil = 0;
  let cooldownTimer = null;
  let history = []; // In-memory session history: [{ role: 'user'|'model', text: string }]

  let els = {};

  function t(key, fallback) {
    return window.i18n?.t ? window.i18n.t(key) : fallback;
  }

  function escapeHtml(str) {
    return String(str ?? '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /**
   * Safely format assistant responses (escapes HTML first, then formats
   * inline code, bold text, and line breaks).
   */
  function formatMessageHtml(text, allowFormatting = true) {
    const escaped = escapeHtml(text);
    if (!allowFormatting) {
      return escaped.replace(/\n/g, '<br>');
    }
    return escaped
      .replace(/`([^`\n]+)`/g, '<code>$1</code>')
      .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
      .replace(/\n/g, '<br>');
  }

  function getActiveContext() {
    const lang = window.i18n?.getCurrentLanguage ? window.i18n.getCurrentLanguage() : 'en';
    const authScreen = document.getElementById('auth-screen');
    const isAuthVisible = authScreen && !authScreen.classList.contains('hidden');

    let tab = 'auth';
    if (!isAuthVisible) {
      const activeBtn = document.querySelector('.tab-btn.active');
      tab = activeBtn?.dataset?.tab || 'dashboard';
    }

    return { lang, tab };
  }

  function scrollToBottom() {
    if (!els.messages) return;
    requestAnimationFrame(() => {
      els.messages.scrollTop = els.messages.scrollHeight;
    });
  }

  function appendMessage(type, text, options = {}) {
    if (!els.messages) return null;

    const wrapper = document.createElement('div');
    wrapper.className = `chatbot-msg chatbot-msg--${type}`;
    if (options.isWelcome) {
      wrapper.dataset.welcome = 'true';
    }

    const bubble = document.createElement('div');
    bubble.className = 'chatbot-bubble';
    bubble.innerHTML = formatMessageHtml(text, type === 'bot');

    wrapper.appendChild(bubble);
    els.messages.appendChild(wrapper);
    scrollToBottom();
    return wrapper;
  }

  function showWelcomeIfNeeded() {
    if (hasWelcome || !els.messages) return;
    appendMessage('bot', t('chatbot.welcome', 'Hi! How can I help you navigate DSS University?'), {
      isWelcome: true,
    });
    hasWelcome = true;
  }

  function showTypingIndicator() {
    if (!els.messages) return null;
    const wrapper = document.createElement('div');
    wrapper.className = 'chatbot-msg chatbot-msg--bot';
    wrapper.id = 'chatbot-typing-indicator';
    wrapper.setAttribute('aria-label', t('chatbot.typing', 'Thinking...'));

    wrapper.innerHTML = `
      <div class="chatbot-bubble chatbot-typing">
        <span class="chatbot-typing__dot"></span>
        <span class="chatbot-typing__dot"></span>
        <span class="chatbot-typing__dot"></span>
      </div>
    `;

    els.messages.appendChild(wrapper);
    scrollToBottom();
    return wrapper;
  }

  function removeTypingIndicator() {
    document.getElementById('chatbot-typing-indicator')?.remove();
  }

  function isInCooldown() {
    return Date.now() < cooldownUntil;
  }

  function startCooldown() {
    cooldownUntil = Date.now() + COOLDOWN_MS;
    if (cooldownTimer) {
      clearTimeout(cooldownTimer);
    }
    updateInputUI();
    cooldownTimer = setTimeout(() => {
      cooldownTimer = null;
      updateInputUI();
    }, COOLDOWN_MS);
  }

  function updateInputUI() {
    if (!els.input || !els.send) return;
    const value = els.input.value;
    const trimmed = value.trim();

    els.send.disabled =
      isLoading || isInCooldown() || trimmed.length === 0 || value.length > MAX_MESSAGE_LENGTH;

    // Auto-resize textarea cleanly
    els.input.style.height = 'auto';
    const targetHeight = Math.min(els.input.scrollHeight, 96);
    els.input.style.height = `${targetHeight}px`;
    els.input.style.overflowY = els.input.scrollHeight > 96 ? 'auto' : 'hidden';

    if (els.charCount) {
      if (value.length >= 800) {
        els.charCount.textContent = `${value.length}/${MAX_MESSAGE_LENGTH}`;
        els.charCount.classList.remove('hidden');
      } else {
        els.charCount.classList.add('hidden');
      }
    }
  }

  function setOpen(nextOpen) {
    if (!els.window || !els.toggle) return;
    isOpen = Boolean(nextOpen);

    els.window.classList.toggle('is-open', isOpen);
    els.window.setAttribute('aria-hidden', String(!isOpen));
    els.toggle.setAttribute('aria-expanded', String(isOpen));

    const toggleLabel = isOpen
      ? t('chatbot.toggle_close', 'Close AI assistant')
      : t('chatbot.toggle_open', 'Open AI assistant');
    els.toggle.title = toggleLabel;
    els.toggle.setAttribute('aria-label', toggleLabel);

    els.window.setAttribute('aria-label', t('chatbot.title', 'DSS Assistant'));
    els.clear?.setAttribute('aria-label', t('chatbot.clear', 'Clear chat'));
    els.close?.setAttribute('aria-label', t('chatbot.close', 'Close'));
    els.send?.setAttribute('aria-label', t('chatbot.send', 'Send message'));
    els.input?.setAttribute('aria-label', t('chatbot.placeholder', 'Chat message'));

    if (isOpen) {
      showWelcomeIfNeeded();
      scrollToBottom();
      setTimeout(() => els.input?.focus(), 120);
    }
  }

  function clearConversation() {
    if (isLoading) return;
    history = [];
    hasWelcome = false;
    if (els.messages) {
      els.messages.innerHTML = '';
    }
    showWelcomeIfNeeded();
    els.input?.focus();
  }

  async function sendMessage(rawText) {
    const message = (rawText ?? '').trim();

    if (!message) {
      appendMessage('error', t('chatbot.error_empty', 'Please type a message first.'));
      return;
    }

    if (message.length > MAX_MESSAGE_LENGTH) {
      appendMessage('error', t('chatbot.error_length', 'Message cannot exceed 1000 characters.'));
      return;
    }

    if (isLoading) return;

    if (isInCooldown()) {
      appendMessage('error', t('chatbot.error_cooldown', 'Please wait a moment before sending another message.'));
      return;
    }

    // Snapshot prior history before adding the current turn
    const historyToSend = history.slice(-MAX_HISTORY_TURNS);

    appendMessage('user', message);
    els.input.value = '';
    isLoading = true;
    updateInputUI();
    showTypingIndicator();

    try {
      const res = await window.app.api('chat.php', 'POST', {
        message,
        history: historyToSend,
        context: getActiveContext(),
      });

      removeTypingIndicator();

      const reply = res?.data?.reply;
      if (res?.success && typeof reply === 'string' && reply.trim() !== '') {
        const cleanReply = reply.trim();
        appendMessage('bot', cleanReply);
        history.push({ role: 'user', text: message });
        history.push({ role: 'model', text: cleanReply });
        if (history.length > MAX_HISTORY_TURNS) {
          history = history.slice(-MAX_HISTORY_TURNS);
        }
      } else {
        appendMessage(
          'error',
          res?.message || t('chatbot.error_network', 'Unable to reach the assistant right now.')
        );
      }
    } catch (err) {
      removeTypingIndicator();
      let errMsg;
      if (err?.status === 429) {
        errMsg = err?.message || t('chatbot.error_rate_limit', 'Too many requests right now. Please wait a moment and try again.');
      } else if (err?.message && !err.message.startsWith('HTTP ')) {
        errMsg = err.message;
      } else {
        errMsg = t('chatbot.error_network', 'Unable to reach the assistant right now.');
      }
      appendMessage('error', errMsg);
    } finally {
      isLoading = false;
      startCooldown();
      if (isOpen) {
        els.input?.focus();
      }
    }
  }

  function refreshLanguage() {
    if (!els.toggle) return;
    const toggleLabel = isOpen
      ? t('chatbot.toggle_close', 'Close AI assistant')
      : t('chatbot.toggle_open', 'Open AI assistant');
    els.toggle.title = toggleLabel;
    els.toggle.setAttribute('aria-label', toggleLabel);

    els.window.setAttribute('aria-label', t('chatbot.title', 'DSS Assistant'));
    els.clear?.setAttribute('aria-label', t('chatbot.clear', 'Clear chat'));
    els.close?.setAttribute('aria-label', t('chatbot.close', 'Close'));
    els.send?.setAttribute('aria-label', t('chatbot.send', 'Send message'));
    els.input?.setAttribute('aria-label', t('chatbot.placeholder', 'Chat message'));

    // If no conversation turns have occurred yet, refresh the welcome bubble text
    if (history.length === 0 && els.messages) {
      const welcomeBubble = els.messages.querySelector('[data-welcome="true"] .chatbot-bubble');
      if (welcomeBubble) {
        welcomeBubble.innerHTML = formatMessageHtml(
          t('chatbot.welcome', 'Hi! How can I help you navigate DSS University?'),
          true
        );
      }
    }
  }

  function init() {
    els = {
      widget: document.getElementById('chatbot-widget'),
      window: document.getElementById('chatbot-window'),
      toggle: document.getElementById('chatbot-toggle'),
      close: document.getElementById('chatbot-close'),
      clear: document.getElementById('chatbot-clear'),
      messages: document.getElementById('chatbot-messages'),
      form: document.getElementById('chatbot-form'),
      input: document.getElementById('chatbot-input'),
      send: document.getElementById('chatbot-send'),
      charCount: document.getElementById('chatbot-char-count'),
    };

    if (!els.widget || !els.window || !els.toggle || !els.form || !els.input) {
      return;
    }

    els.toggle.addEventListener('click', () => setOpen(!isOpen));
    els.close?.addEventListener('click', () => setOpen(false));
    els.clear?.addEventListener('click', clearConversation);

    els.input.addEventListener('input', updateInputUI);
    els.input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (!els.send.disabled) {
          sendMessage(els.input.value);
        }
      }
    });

    els.form.addEventListener('submit', (e) => {
      e.preventDefault();
      sendMessage(els.input.value);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isOpen) {
        setOpen(false);
      }
    });

    refreshLanguage();
    updateInputUI();
  }

  return { init, open: () => setOpen(true), close: () => setOpen(false), refreshLanguage };
})();

window.chatbot = chatbot;

document.addEventListener('DOMContentLoaded', () => chatbot.init());
