/**
 * Evante AI Chat — chat.js
 * Handles messaging, typing indicator, markdown, file uploads, quick replies.
 */

/* ─── Simple Markdown Renderer ──────────────────────────────────────── */
function renderMarkdown(text) {
    if (typeof window.marked !== 'undefined') {
        return window.marked.parse(text);
    }

    // Fallback: basic inline renderer
    const escape = (s) =>
        s.replace(/&/g, '&amp;')
         .replace(/</g, '&lt;')
         .replace(/>/g, '&gt;');

    let html = escape(text);

    // Tables (| col | col |)
    html = html.replace(/\|(.+)\|\n\|[-| :]+\|\n((?:\|.+\|\n?)+)/g, (_, header, body) => {
        const th = header.split('|').filter(c => c.trim()).map(c => `<th>${c.trim()}</th>`).join('');
        const rows = body.trim().split('\n').map(row => {
            const tds = row.split('|').filter(c => c.trim()).map(c => `<td>${c.trim()}</td>`).join('');
            return `<tr>${tds}</tr>`;
        }).join('');
        return `<table class="chat-table"><thead><tr>${th}</tr></thead><tbody>${rows}</tbody></table>`;
    });

    // Headings
    html = html.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    html = html.replace(/^## (.+)$/gm,  '<h2>$1</h2>');
    html = html.replace(/^# (.+)$/gm,   '<h1>$1</h1>');

    // Images: ![alt](url)  — must be before links
    html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" class="msg-markdown-img" loading="lazy">');

    // Links: [text](url)
    html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

    // Bold / italic
    html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.+?)\*/g,     '<em>$1</em>');

    // Inline code
    html = html.replace(/`(.+?)`/g, '<code>$1</code>');

    // Blockquote
    html = html.replace(/^&gt; (.+)$/gm, '<blockquote>$1</blockquote>');

    // Unordered lists
    html = html.replace(/((?:^[-*] .+\n?)+)/gm, (block) => {
        const items = block.trim().split('\n')
            .map(l => `<li>${l.replace(/^[-*] /, '')}</li>`)
            .join('');
        return `<ul>${items}</ul>`;
    });

    // Numbered lists
    html = html.replace(/((?:^\d+[️⃣]? .+\n?)+)/gm, (block) => {
        const items = block.trim().split('\n')
            .map(l => `<li>${l.replace(/^\d+[️⃣]? /, '')}</li>`)
            .join('');
        return `<ol>${items}</ol>`;
    });

    // Paragraphs (double newline)
    html = html.split(/\n{2,}/).map(p => {
        p = p.trim();
        if (!p) return '';
        if (/^<(h[1-6]|ul|ol|table|blockquote)/.test(p)) return p;
        return `<p>${p.replace(/\n/g, '<br>')}</p>`;
    }).join('');

    return html;
}

/* ─── Utility ────────────────────────────────────────────────────────── */
function formatTime(iso) {
    const d = iso ? new Date(iso) : null;
    const valid = d && !isNaN(d.getTime());
    return (valid ? d : new Date()).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
}

function formatRelative(iso) {
    const d = new Date(iso);
    const diff = (Date.now() - d) / 1000;
    if (diff < 60)   return 'เมื่อกี้';
    if (diff < 3600) return `${Math.floor(diff / 60)} นาทีที่แล้ว`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} ชม.ที่แล้ว`;
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/* ─── ChatManager ────────────────────────────────────────────────────── */
class ChatManager {
    constructor() {
        this.sessionId    = this.getOrCreateSessionId();
        this.pendingFile  = null;
        this.isTyping     = false;
        this.typingEl     = null;
        this.messageCount = 0;

        this.messagesEl   = document.getElementById('chatMessages');
        this.inputEl      = document.getElementById('messageInput');
        this.sendBtn      = document.getElementById('sendBtn');
        this.fileInput    = document.getElementById('fileInput');
        this.filePreview  = document.getElementById('filePreview');
        this.quickReplies = document.getElementById('quickReplies');
        this.charCount    = document.getElementById('charCount');

        this.init();
    }

    /* ── Session ──────────────────────────────────────────────────── */
    getOrCreateSessionId() {
        let id = localStorage.getItem('evante_chat_session');
        if (!id) {
            id = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
            localStorage.setItem('evante_chat_session', id);
        }
        return id;
    }

    /* ── Init ─────────────────────────────────────────────────────── */
    init() {
        this.bindEvents();
        this.loadSessions();

        // Show welcome message if first time
        const history = this.getHistory();
        if (history.length === 0) {
            setTimeout(() => this.showWelcome(), 300);
        } else {
            history.forEach(msg => this.renderMessage(msg.role, msg.text, msg.imageUrl, [], msg.time, false));
            this.scrollToBottom(false);
        }
    }

    bindEvents() {
        // Send on button click
        this.sendBtn.addEventListener('click', () => this.submitMessage());

        // Send on Enter (Shift+Enter = newline)
        this.inputEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.submitMessage();
            }
        });

        // Char counter
        this.inputEl.addEventListener('input', () => {
            const len = this.inputEl.value.length;
            if (this.charCount) this.charCount.textContent = len;
        });

        // File input
        this.fileInput?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) this.handleFileSelect(file);
        });

        // Drag & drop on messages area
        this.messagesEl.addEventListener('dragover', (e) => { e.preventDefault(); this.messagesEl.classList.add('drag-over'); });
        this.messagesEl.addEventListener('dragleave', () => this.messagesEl.classList.remove('drag-over'));
        this.messagesEl.addEventListener('drop', (e) => {
            e.preventDefault();
            this.messagesEl.classList.remove('drag-over');
            const file = e.dataTransfer.files[0];
            if (file) this.handleFileSelect(file);
        });

        // New chat
        document.getElementById('newChatBtn')?.addEventListener('click', () => this.newChat());

        // Mobile sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', () => this.toggleSidebar());
        document.getElementById('sidebarOverlay')?.addEventListener('click', () => this.closeSidebar());
    }

    /* ── Sending ──────────────────────────────────────────────────── */
    async submitMessage() {
        const text = this.inputEl.value.trim();
        const hasFile = !!this.pendingFile;

        if (!text && !hasFile) return;
        if (this.isTyping) return;

        let imageUrl = null;
        this.inputEl.value = '';
        if (this.charCount) this.charCount.textContent = '0';
        this.clearQuickReplies();

        if (hasFile) {
            imageUrl = await this.uploadFile(this.pendingFile);
            this.clearFilePreview();
        }

        // Render user message
        this.renderMessage('user', text, imageUrl, [], null, true);
        this.saveToHistory('user', text, imageUrl);

        // Send to server
        await this.fetchResponse(text, imageUrl);
    }

    async fetchResponse(message, imageUrl) {
        this.showTyping();

        try {
            const res = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message, image_url: imageUrl, session_id: this.sessionId }),
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();

            this.hideTyping();
            this.renderMessage('bot', data.message, null, data.quick_replies ?? [], data.timestamp, true);
            this.saveToHistory('bot', data.message, null, data.timestamp);

            if (data.quick_replies?.length) {
                this.showQuickReplies(data.quick_replies);
            }
        } catch (err) {
            this.hideTyping();
            this.renderMessage('bot', '⚠️ เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้งครับ', null, ['ลองใหม่'], null, true);
            console.error('Chat error:', err);
        }
    }

    /* ── Rendering ────────────────────────────────────────────────── */
    renderMessage(role, text, imageUrl, quickReplies, timestamp, animate) {
        const isUser = role === 'user';
        const wrapper = document.createElement('div');
        wrapper.className = `msg-row ${isUser ? 'msg-row--user' : 'msg-row--bot'} ${animate ? 'msg-animate' : ''}`;

        const timeStr = formatTime(timestamp);

        let contentHtml = '';

        if (imageUrl) {
            const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(imageUrl);
            if (isImage) {
                contentHtml += `<img src="${imageUrl}" class="msg-image" alt="uploaded image" loading="lazy">`;
            } else {
                contentHtml += `<a href="${imageUrl}" target="_blank" class="msg-file-link"><i class="bi bi-file-earmark"></i> ดูไฟล์แนบ</a>`;
            }
        }

        if (text) {
            if (isUser) {
                // User: plain text with line breaks
                contentHtml += `<p class="msg-text">${text.replace(/\n/g, '<br>')}</p>`;
            } else {
                // Bot: render markdown
                contentHtml += `<div class="msg-markdown">${renderMarkdown(text)}</div>`;
            }
        }

        wrapper.innerHTML = isUser
            ? `<div class="msg-spacer"></div>
               <div class="msg-body">
                 <div class="msg-bubble msg-bubble--user">${contentHtml}</div>
                 <div class="msg-meta">${timeStr}</div>
               </div>
               <div class="msg-avatar msg-avatar--user"><i class="bi bi-person-fill"></i></div>`
            : `<div class="msg-avatar msg-avatar--bot">🤖</div>
               <div class="msg-body">
                 <div class="msg-bubble msg-bubble--bot">${contentHtml}</div>
                 <div class="msg-meta">${timeStr}</div>
               </div>
               <div class="msg-spacer"></div>`;

        this.messagesEl.appendChild(wrapper);
        this.scrollToBottom(animate);
        this.messageCount++;
    }

    showTyping() {
        if (this.isTyping) return;
        this.isTyping = true;

        const el = document.createElement('div');
        el.className = 'msg-row msg-row--bot msg-animate';
        el.id = 'typingIndicator';
        el.innerHTML = `
            <div class="msg-avatar msg-avatar--bot">🤖</div>
            <div class="msg-body">
              <div class="msg-bubble msg-bubble--bot typing-bubble">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
              </div>
            </div>
            <div class="msg-spacer"></div>`;

        this.messagesEl.appendChild(el);
        this.typingEl = el;
        this.scrollToBottom(true);
    }

    hideTyping() {
        this.isTyping = false;
        this.typingEl?.remove();
        this.typingEl = null;
    }

    scrollToBottom(smooth = true) {
        this.messagesEl.scrollTo({
            top: this.messagesEl.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant',
        });
    }

    /* ── Quick Replies ────────────────────────────────────────────── */
    showQuickReplies(replies) {
        if (!this.quickReplies) return;
        this.quickReplies.innerHTML = replies.map(r =>
            `<button class="qr-btn" type="button" data-text="${r}">${r}</button>`
        ).join('');
        this.quickReplies.classList.remove('hidden');

        this.quickReplies.querySelectorAll('.qr-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.inputEl.value = btn.dataset.text;
                this.clearQuickReplies();
                this.submitMessage();
            });
        });
    }

    clearQuickReplies() {
        if (this.quickReplies) {
            this.quickReplies.innerHTML = '';
            this.quickReplies.classList.add('hidden');
        }
    }

    /* ── File Handling ────────────────────────────────────────────── */
    handleFileSelect(file) {
        this.pendingFile = file;
        if (!this.filePreview) return;

        const isImage = file.type.startsWith('image/');
        const sizeKB = Math.round(file.size / 1024);

        if (isImage) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.filePreview.innerHTML = `
                    <div class="file-preview-inner">
                      <img src="${e.target.result}" class="preview-thumb" alt="preview">
                      <div class="preview-info">
                        <span class="preview-name">${file.name}</span>
                        <span class="preview-size">${sizeKB} KB</span>
                      </div>
                      <button class="preview-remove" id="removeFile" type="button"><i class="bi bi-x-circle-fill"></i></button>
                    </div>`;
                this.filePreview.classList.remove('hidden');
                document.getElementById('removeFile')?.addEventListener('click', () => this.clearFilePreview());
            };
            reader.readAsDataURL(file);
        } else {
            this.filePreview.innerHTML = `
                <div class="file-preview-inner">
                  <div class="preview-icon"><i class="bi bi-file-earmark-text"></i></div>
                  <div class="preview-info">
                    <span class="preview-name">${file.name}</span>
                    <span class="preview-size">${sizeKB} KB</span>
                  </div>
                  <button class="preview-remove" id="removeFile" type="button"><i class="bi bi-x-circle-fill"></i></button>
                </div>`;
            this.filePreview.classList.remove('hidden');
            document.getElementById('removeFile')?.addEventListener('click', () => this.clearFilePreview());
        }
    }

    clearFilePreview() {
        this.pendingFile = null;
        if (this.filePreview) {
            this.filePreview.innerHTML = '';
            this.filePreview.classList.add('hidden');
        }
        if (this.fileInput) this.fileInput.value = '';
    }

    async uploadFile(file) {
        const form = new FormData();
        form.append('file', file);

        try {
            const res = await fetch('/chat/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
                body: form,
            });
            if (!res.ok) throw new Error('Upload failed');
            const data = await res.json();
            return data.url;
        } catch {
            return null;
        }
    }

    /* ── Session / History ────────────────────────────────────────── */
    getHistory() {
        try {
            return JSON.parse(localStorage.getItem(`chat_history_${this.sessionId}`) ?? '[]');
        } catch { return []; }
    }

    saveToHistory(role, text, imageUrl, time) {
        const history = this.getHistory();
        history.push({ role, text, imageUrl: imageUrl ?? null, time: time ?? new Date().toISOString() });
        // Keep last 50 messages
        if (history.length > 50) history.splice(0, history.length - 50);
        localStorage.setItem(`chat_history_${this.sessionId}`, JSON.stringify(history));
        this.updateSessionPreview(text || 'รูปภาพ');
    }

    updateSessionPreview(text) {
        const sessions = this.getSessions();
        const existing = sessions.find(s => s.id === this.sessionId);
        if (existing) {
            existing.preview = text.substring(0, 40);
            existing.updated_at = new Date().toISOString();
        } else {
            sessions.unshift({
                id: this.sessionId,
                title: text.substring(0, 30) || 'การสนทนาใหม่',
                preview: text.substring(0, 40),
                updated_at: new Date().toISOString(),
            });
        }
        if (sessions.length > 10) sessions.splice(10);
        localStorage.setItem('evante_chat_sessions', JSON.stringify(sessions));
        this.renderSessions(sessions);
    }

    getSessions() {
        try {
            return JSON.parse(localStorage.getItem('evante_chat_sessions') ?? '[]');
        } catch { return []; }
    }

    loadSessions() {
        const sessions = this.getSessions();
        this.renderSessions(sessions);
    }

    renderSessions(sessions) {
        const list = document.getElementById('sessionList');
        if (!list) return;

        if (sessions.length === 0) {
            list.innerHTML = '<p class="session-empty">ยังไม่มีประวัติการสนทนา</p>';
            return;
        }

        list.innerHTML = sessions.map(s => `
            <button class="session-item ${s.id === this.sessionId ? 'active' : ''}" data-id="${s.id}" type="button">
              <div class="session-icon"><i class="bi bi-chat-left-text"></i></div>
              <div class="session-info">
                <span class="session-title">${s.title}</span>
                <span class="session-preview">${s.preview ?? ''}</span>
              </div>
              <span class="session-time">${formatRelative(s.updated_at)}</span>
            </button>`
        ).join('');

        list.querySelectorAll('.session-item').forEach(btn => {
            btn.addEventListener('click', () => this.switchSession(btn.dataset.id));
        });
    }

    switchSession(id) {
        if (id === this.sessionId) { this.closeSidebar(); return; }
        this.sessionId = id;
        localStorage.setItem('evante_chat_session', id);
        this.messagesEl.innerHTML = '';
        this.messageCount = 0;
        this.clearQuickReplies();
        this.clearFilePreview();

        const history = this.getHistory();
        if (history.length === 0) {
            this.showWelcome();
        } else {
            history.forEach(msg => this.renderMessage(msg.role, msg.text, msg.imageUrl, [], msg.time, false));
            this.scrollToBottom(false);
        }
        this.loadSessions();
        this.closeSidebar();
    }

    newChat() {
        this.sessionId = 'sess_' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
        localStorage.setItem('evante_chat_session', this.sessionId);
        this.messagesEl.innerHTML = '';
        this.messageCount = 0;
        this.clearQuickReplies();
        this.clearFilePreview();
        this.showWelcome();
        this.loadSessions();
        this.closeSidebar();
    }

    showWelcome() {
        setTimeout(() => {
            this.renderMessage(
                'bot',
                'สวัสดีค่ะ! 👋 ยินดีต้อนรับสู่ **Evante Property Assistant**\n\nเอวองพร้อมช่วยคุณค้นหาห้อง, ดูราคา, นัดชม, และคำนวณสินเชื่อค่ะ\n\nวันนี้ต้องการให้ช่วยเรื่องอะไรคะ?',
                null,
                ['ดูห้องว่าง', 'โปรโมชั่น', 'นัดชมห้อง', 'คำนวณสินเชื่อ'],
                null,
                true
            );
            this.showQuickReplies(['ดูห้องว่าง', 'โปรโมชั่น', 'นัดชมห้อง', 'คำนวณสินเชื่อ']);
        }, 400);
    }

    /* ── Sidebar ──────────────────────────────────────────────────── */
    toggleSidebar() {
        const sidebar = document.getElementById('chatSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar?.classList.toggle('open');
        overlay?.classList.toggle('visible');
    }

    closeSidebar() {
        document.getElementById('chatSidebar')?.classList.remove('open');
        document.getElementById('sidebarOverlay')?.classList.remove('visible');
    }
}

/* ─── Bootstrap ──────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    window.chatManager = new ChatManager();
});
