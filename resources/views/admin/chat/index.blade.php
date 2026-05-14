<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Chat | Evante</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>

    {{-- Laravel Echo + Pusher via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.js"></script>

    <style>
        :root {
            --primary:       #2A8B92;
            --primary-dark:  #1e6b71;
            --primary-light: #3aa8b0;
            --primary-muted: rgba(42,139,146,0.1);
            --cream:         #F7EFE2;
            --surface:       #ffffff;
            --bg:            #f5f2ee;
            --border:        #e8e2d9;
            --sidebar-bg:    #1a2e35;
            --text-dark:     #1a2e35;
            --text-mid:      #3d5a61;
            --text-light:    #6b8c93;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-dark); }

        /* Layout */
        .admin-chat-layout { display: flex; height: 100vh; overflow: hidden; }

        /* Topbar */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0; height: 56px; z-index: 200;
            background: var(--sidebar-bg);
            display: flex; align-items: center; gap: 14px; padding: 0 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .topbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1rem; color: #fff; font-weight: 700; letter-spacing: 0.3px;
        }
        .topbar-brand span {
            font-family: 'Inter', sans-serif;
            font-size: 0.65rem; font-weight: 400;
            color: rgba(255,255,255,0.45);
            text-transform: uppercase; letter-spacing: 1.5px; margin-left: 6px;
        }
        .topbar-badge {
            background: var(--primary); color: #fff;
            border-radius: 999px; padding: 2px 10px;
            font-size: 0.72rem; font-weight: 600;
        }
        .topbar-link {
            margin-left: auto; color: rgba(255,255,255,0.6);
            text-decoration: none; font-size: 0.8rem;
            display: flex; align-items: center; gap: 5px;
            transition: color 0.2s;
        }
        .topbar-link:hover { color: #fff; }

        /* Sessions Panel */
        .sessions-panel {
            width: 300px; flex-shrink: 0;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            margin-top: 56px; overflow: hidden;
        }
        .sessions-header {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 8px;
        }
        .sessions-header h6 { font-size: 0.85rem; font-weight: 600; margin: 0; }
        .sessions-filter {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            display: flex; gap: 6px;
        }
        .filter-btn {
            border: 1.5px solid var(--border);
            background: none; border-radius: 999px;
            padding: 4px 12px; font-size: 0.72rem; font-weight: 500;
            cursor: pointer; color: var(--text-mid); transition: all 0.15s;
        }
        .filter-btn.active, .filter-btn:hover {
            background: var(--primary); border-color: var(--primary); color: #fff;
        }
        .sessions-list { flex: 1; overflow-y: auto; }
        .sessions-list::-webkit-scrollbar { width: 3px; }
        .sessions-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

        .session-card {
            padding: 12px 14px; border-bottom: 1px solid var(--border);
            cursor: pointer; transition: background 0.15s;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .session-card:hover { background: var(--bg); }
        .session-card.active { background: var(--primary-muted); border-left: 3px solid var(--primary); }
        .session-avatar {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem;
        }
        .session-info { flex: 1; min-width: 0; }
        .session-name { font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .session-preview { font-size: 0.72rem; color: var(--text-light); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
        .session-meta { display: flex; align-items: center; gap: 5px; margin-top: 4px; }
        .status-badge {
            font-size: 0.62rem; font-weight: 600;
            padding: 1px 7px; border-radius: 999px;
        }
        .status-active  { background: rgba(34,197,94,0.15); color: #16a34a; }
        .status-waiting { background: rgba(245,158,11,0.15); color: #b45309; }
        .status-resolved { background: rgba(100,116,139,0.15); color: #475569; }
        .handled-badge {
            font-size: 0.62rem; padding: 1px 6px; border-radius: 999px;
        }
        .handled-ai    { background: rgba(42,139,146,0.12); color: var(--primary-dark); }
        .handled-admin { background: rgba(99,102,241,0.12); color: #4338ca; }
        .unread-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--primary); margin-left: auto;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:0.4} }
        .session-time { font-size: 0.65rem; color: var(--text-light); white-space: nowrap; }

        .empty-sessions {
            padding: 40px 20px; text-align: center;
            color: var(--text-light); font-size: 0.82rem;
        }

        /* Chat Panel */
        .chat-panel {
            flex: 1; display: flex; flex-direction: column;
            margin-top: 56px; overflow: hidden;
            background: var(--bg);
        }

        /* Chat Header */
        .chat-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 20px; height: 58px;
            display: flex; align-items: center; gap: 12px;
            flex-shrink: 0;
        }
        .chat-header-avatar {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1rem; flex-shrink: 0;
        }
        .chat-header-info h6 { font-size: 0.88rem; font-weight: 600; margin: 0; }
        .chat-header-info p { font-size: 0.7rem; color: var(--text-light); margin: 0; }
        .chat-header-actions { margin-left: auto; display: flex; gap: 8px; }
        .action-btn {
            border: 1.5px solid var(--border); background: none;
            border-radius: 10px; padding: 6px 14px;
            font-size: 0.78rem; font-weight: 500; cursor: pointer;
            display: flex; align-items: center; gap: 6px; transition: all 0.15s;
        }
        .action-btn:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-muted); }
        .action-btn.takeover { border-color: #6366f1; color: #6366f1; }
        .action-btn.takeover:hover { background: rgba(99,102,241,0.08); }
        .action-btn.handback { border-color: var(--primary); color: var(--primary); }
        .action-btn.resolve-btn { border-color: #64748b; color: #64748b; }
        .action-btn.resolve-btn:hover { background: rgba(100,116,139,0.08); }

        /* Messages */
        .chat-messages {
            flex: 1; overflow-y: auto;
            padding: 20px 24px; display: flex; flex-direction: column; gap: 4px;
        }
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

        .msg-row { display: flex; align-items: flex-end; gap: 8px; max-width: 100%; }
        .msg-row--user { flex-direction: row-reverse; }
        .msg-row--ai, .msg-row--admin { flex-direction: row; }
        .msg-spacer { flex: 1; min-width: 32px; max-width: 80px; }
        .msg-animate { animation: msg-in 0.22s ease; }
        @keyframes msg-in { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

        .msg-avatar {
            width: 30px; height: 30px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem; flex-shrink: 0; margin-bottom: 4px;
        }
        .msg-avatar--user  { background: linear-gradient(135deg,#94a3b8,#64748b); color:#fff; font-size:0.75rem; }
        .msg-avatar--ai    { background: linear-gradient(135deg,var(--primary),var(--primary-dark)); color:#fff; font-size:0.9rem; }
        .msg-avatar--admin { background: linear-gradient(135deg,#6366f1,#4338ca); color:#fff; font-size:0.75rem; }

        .msg-body { display: flex; flex-direction: column; max-width: min(580px, 74%); }
        .msg-row--user .msg-body { align-items: flex-end; }
        .msg-row--ai .msg-body, .msg-row--admin .msg-body { align-items: flex-start; }

        .msg-bubble {
            padding: 9px 13px; border-radius: 14px;
            line-height: 1.55; word-break: break-word;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            font-size: 0.855rem;
        }
        .msg-bubble--user  { background: #e2e8f0; color: var(--text-dark); border-bottom-right-radius: 4px; }
        .msg-bubble--ai    { background: var(--surface); border: 1px solid var(--border); color: var(--text-dark); border-bottom-left-radius: 4px; }
        .msg-bubble--admin { background: linear-gradient(135deg,#6366f1,#4338ca); color:#fff; border-bottom-left-radius: 4px; }
        .msg-bubble--system { background: rgba(42,139,146,0.08); border: 1px dashed var(--primary); color: var(--text-mid); font-size: 0.78rem; text-align: center; border-radius: 10px; padding: 7px 14px; margin: 6px auto; max-width: 320px; }

        .msg-meta { font-size: 0.64rem; color: var(--text-light); margin-top: 3px; padding: 0 2px; }
        .msg-sender-label { font-size: 0.65rem; color: var(--text-light); margin-bottom: 2px; font-weight: 500; }

        .msg-markdown { font-size: 0.855rem; }
        .msg-markdown p { margin: 0 0 7px; line-height: 1.6; }
        .msg-markdown p:last-child { margin-bottom: 0; }
        .msg-markdown strong { font-weight: 600; }
        .msg-markdown ul, .msg-markdown ol { padding-left: 18px; margin: 5px 0; }
        .msg-markdown li { margin-bottom: 3px; }
        .msg-markdown img { max-width: 100%; max-height: 200px; border-radius: 8px; margin: 5px 0; display:block; }
        .msg-markdown a { color: var(--primary); text-decoration: underline; }

        /* Empty state */
        .chat-empty-state {
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            height: 100%; gap: 12px; color: var(--text-light);
            text-align: center; padding: 32px;
        }
        .chat-empty-state .empty-icon { font-size: 3rem; }
        .chat-empty-state p { font-size: 0.85rem; }

        /* Input */
        .chat-input-area {
            background: var(--surface); border-top: 1px solid var(--border);
            padding: 12px 20px; flex-shrink: 0;
        }
        .input-wrapper {
            display: flex; align-items: flex-end; gap: 8px;
            background: var(--bg); border: 1.5px solid var(--border);
            border-radius: 14px; padding: 8px 8px 8px 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrapper:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(42,139,146,0.1); }
        .admin-input {
            flex: 1; background: none; border: none; outline: none;
            resize: none; font-family: 'Inter', sans-serif;
            font-size: 0.875rem; color: var(--text-dark); line-height: 1.5;
            max-height: 120px; overflow-y: auto; padding: 2px 0;
        }
        .admin-input::placeholder { color: var(--text-light); }
        .btn-admin-send {
            width: 38px; height: 38px; border-radius: 11px; border: none;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            color: #fff; font-size: 0.95rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s; flex-shrink: 0;
        }
        .btn-admin-send:hover { transform: scale(1.06); }
        .btn-admin-send:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .input-hint { font-size: 0.66rem; color: var(--text-light); margin-top: 5px; text-align: right; }

        .no-session-selected {
            display: flex; flex-direction: column; align-items: center;
            justify-content: center; height: 100%; gap: 10px;
            color: var(--text-light); font-size: 0.85rem;
        }
        .no-session-selected .icon { font-size: 2.5rem; }

        /* Notification sound visual indicator */
        .notification-flash { animation: flash-anim 0.4s ease; }
        @keyframes flash-anim { 0%{background:rgba(42,139,146,0.2)} 100%{background:transparent} }
    </style>
</head>
<body>

{{-- Topbar --}}
<div class="topbar">
    <div class="topbar-brand">
        Evante <span>Admin Chat</span>
    </div>
    <span class="topbar-badge" id="totalUnread" style="display:none">0</span>
    <a href="{{ url('/dashboard') }}" class="topbar-link">
        <i class="bi bi-house"></i> Dashboard
    </a>
</div>

<div class="admin-chat-layout">

    {{-- Sessions Panel --}}
    <div class="sessions-panel">
        <div class="sessions-header">
            <i class="bi bi-chat-dots" style="color:var(--primary);font-size:1.1rem"></i>
            <h6>Sessions</h6>
            <button class="ms-auto btn p-0" style="color:var(--text-light);font-size:0.8rem" onclick="AdminChat.loadSessions()" title="รีเฟรช">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
        <div class="sessions-filter">
            <button class="filter-btn active" data-filter="all" onclick="AdminChat.filterSessions(this)">ทั้งหมด</button>
            <button class="filter-btn" data-filter="waiting" onclick="AdminChat.filterSessions(this)">รอ Admin</button>
            <button class="filter-btn" data-filter="active" onclick="AdminChat.filterSessions(this)">Active</button>
        </div>
        <div class="sessions-list" id="sessionsList">
            <div class="empty-sessions"><i class="bi bi-hourglass" style="font-size:1.5rem;margin-bottom:8px;display:block"></i>กำลังโหลด...</div>
        </div>
    </div>

    {{-- Chat Panel --}}
    <div class="chat-panel" id="chatPanel">

        {{-- Default empty state --}}
        <div class="no-session-selected" id="noSessionState">
            <div class="icon"><i class="bi bi-chat-square-text"></i></div>
            <p>เลือก Session เพื่อเริ่มดูการสนทนา</p>
        </div>

        {{-- Chat header (hidden until session selected) --}}
        <div class="chat-header" id="chatHeader" style="display:none">
            <div class="chat-header-avatar" id="headerAvatar"><i class="bi bi-person"></i></div>
            <div class="chat-header-info">
                <h6 id="headerName">—</h6>
                <p id="headerStatus">—</p>
            </div>
            <div class="chat-header-actions">
                <button class="action-btn takeover" id="btnTakeover" onclick="AdminChat.takeover()">
                    <i class="bi bi-person-check"></i> Take Over
                </button>
                <button class="action-btn handback" id="btnHandback" onclick="AdminChat.handback()" style="display:none">
                    <i class="bi bi-robot"></i> ส่งให้ AI
                </button>
                <button class="action-btn resolve-btn" onclick="AdminChat.resolve()">
                    <i class="bi bi-check2-circle"></i> Resolved
                </button>
            </div>
        </div>

        {{-- Messages area --}}
        <div class="chat-messages" id="chatMessages" style="display:none"></div>

        {{-- Input area --}}
        <div class="chat-input-area" id="chatInputArea" style="display:none">
            <div class="input-wrapper">
                <textarea
                    class="admin-input"
                    id="adminInput"
                    rows="1"
                    placeholder="พิมพ์ข้อความตอบลูกค้า... (Enter เพื่อส่ง)"
                    maxlength="2000"
                    disabled
                ></textarea>
                <button class="btn-admin-send" id="adminSendBtn" onclick="AdminChat.send()" disabled>
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
            <div class="input-hint">Enter เพื่อส่ง · Shift+Enter ขึ้นบรรทัด</div>
        </div>

    </div>
</div>

{{-- Notification sound --}}
<audio id="notifySound" preload="auto">
    <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAA..." type="audio/wav">
</audio>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const REVERB_KEY    = '{{ env("REVERB_APP_KEY") }}';
const REVERB_HOST   = '{{ env("REVERB_HOST", "localhost") }}';
const REVERB_PORT   = {{ env("REVERB_PORT", 8080) }};
const REVERB_SCHEME = '{{ env("REVERB_SCHEME", "http") }}';

// ─── Echo Setup ───────────────────────────────────────────────────────────
let echo = null;
if (typeof Echo !== 'undefined' && REVERB_KEY) {
    echo = new Echo({
        broadcaster: 'reverb',
        key: REVERB_KEY,
        wsHost: REVERB_HOST,
        wsPort: REVERB_PORT,
        wssPort: REVERB_PORT,
        forceTLS: REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': CSRF } },
    });
}

// ─── Utility ──────────────────────────────────────────────────────────────
function csrfFetch(url, opts = {}) {
    return fetch(url, {
        ...opts,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
            ...(opts.headers ?? {}),
        },
    });
}

function formatTime(iso) {
    const d = iso ? new Date(iso) : new Date();
    return d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
}

function formatRelative(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    const diff = (Date.now() - d) / 1000;
    if (diff < 60)    return 'เมื่อกี้';
    if (diff < 3600)  return `${Math.floor(diff / 60)} นาทีที่แล้ว`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} ชม.ที่แล้ว`;
    return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
}

function renderMarkdown(text) {
    if (typeof window.marked !== 'undefined') return window.marked.parse(text);
    return text.replace(/\n/g, '<br>');
}

function playNotify() {
    try {
        const ctx = new AudioContext();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 880;
        osc.type = 'sine';
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        osc.start(); osc.stop(ctx.currentTime + 0.3);
    } catch(e) {}
}

// ─── AdminChat ────────────────────────────────────────────────────────────
const AdminChat = {
    sessions: [],
    currentFilter: 'all',
    currentSessionId: null,
    currentHandledBy: null,
    echoChannel: null,
    renderedIds: new Set(),

    async loadSessions() {
        try {
            const res = await csrfFetch('/admin/chat/sessions');
            const data = await res.json();
            this.sessions = data.sessions ?? [];
            this.renderSessions();
            this.updateUnreadBadge();
        } catch(e) {
            console.error('loadSessions error', e);
        }
    },

    filterSessions(btn) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        this.currentFilter = btn.dataset.filter;
        this.renderSessions();
    },

    renderSessions() {
        const list = document.getElementById('sessionsList');
        let filtered = this.sessions;
        if (this.currentFilter !== 'all') {
            filtered = this.sessions.filter(s =>
                this.currentFilter === 'waiting'
                    ? s.status === 'waiting'
                    : s.status === this.currentFilter
            );
        }

        if (filtered.length === 0) {
            list.innerHTML = `<div class="empty-sessions"><i class="bi bi-inbox" style="font-size:1.5rem;margin-bottom:8px;display:block"></i>ไม่มี session</div>`;
            return;
        }

        list.innerHTML = filtered.map(s => {
            const isActive = s.id === this.currentSessionId;
            const statusClass = `status-${s.status}`;
            const handledClass = `handled-${s.handled_by}`;
            const statusLabel = s.status === 'active' ? 'Active' : s.status === 'waiting' ? 'รอ Admin' : 'Resolved';
            const handledLabel = s.handled_by === 'ai' ? '🤖 AI' : '👤 Admin';
            const unreadDot = s.unread > 0 ? `<span class="unread-dot ms-auto"></span>` : '';

            return `
            <div class="session-card ${isActive ? 'active' : ''}" data-id="${s.id}" onclick="AdminChat.selectSession(${s.id})">
                <div class="session-avatar"><i class="bi bi-person"></i></div>
                <div class="session-info">
                    <div class="session-name">${s.customer_name}</div>
                    <div class="session-preview">${s.last_message ? s.last_message.substring(0, 45) : '—'}</div>
                    <div class="session-meta">
                        <span class="status-badge ${statusClass}">${statusLabel}</span>
                        <span class="status-badge ${handledClass}">${handledLabel}</span>
                        <span class="session-time">${formatRelative(s.last_message_at)}</span>
                        ${unreadDot}
                    </div>
                </div>
            </div>`;
        }).join('');
    },

    updateUnreadBadge() {
        const total = this.sessions.reduce((sum, s) => sum + (s.unread || 0), 0);
        const badge = document.getElementById('totalUnread');
        if (total > 0) {
            badge.textContent = total;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    },

    async selectSession(sessionId) {
        this.currentSessionId = sessionId;

        // Unsubscribe previous channel
        if (this.echoChannel && echo) {
            echo.leave(`chat.${this.currentSessionId}`);
            this.echoChannel = null;
        }

        // Update UI
        document.getElementById('noSessionState').style.display = 'none';
        document.getElementById('chatHeader').style.display = '';
        document.getElementById('chatMessages').style.display = '';
        document.getElementById('chatInputArea').style.display = '';

        // Highlight selected card
        document.querySelectorAll('.session-card').forEach(c =>
            c.classList.toggle('active', parseInt(c.dataset.id) === sessionId)
        );

        // Load messages
        await this.loadMessages(sessionId);

        // Subscribe to echo
        this.subscribeSession(sessionId);
    },

    async loadMessages(sessionId) {
        const msgEl = document.getElementById('chatMessages');
        msgEl.innerHTML = `<div style="text-align:center;padding:20px;color:var(--text-light);font-size:0.82rem"><i class="bi bi-hourglass"></i> กำลังโหลด...</div>`;

        const res = await csrfFetch(`/admin/chat/sessions/${sessionId}/messages`);
        const data = await res.json();

        const session = data.session;
        this.currentHandledBy = session.handled_by;
        this.updateHeaderUI(sessionId, session);

        msgEl.innerHTML = '';
        this.renderedIds.clear();
        const sorted = (data.messages || []).sort((a, b) =>
            new Date(a.timestamp || 0) - new Date(b.timestamp || 0)
        );
        sorted.forEach(m => this.appendMessage(m, false));
        this.scrollToBottom();
    },

    updateHeaderUI(sessionId, session) {
        const s = this.sessions.find(x => x.id === sessionId);
        document.getElementById('headerName').textContent = s ? s.customer_name : `Session #${sessionId}`;
        document.getElementById('headerStatus').textContent = `Channel: ${s?.channel ?? 'web'} · Status: ${session.status}`;

        const btnTakeover = document.getElementById('btnTakeover');
        const btnHandback = document.getElementById('btnHandback');
        const input       = document.getElementById('adminInput');
        const sendBtn     = document.getElementById('adminSendBtn');

        if (session.handled_by === 'admin') {
            btnTakeover.style.display = 'none';
            btnHandback.style.display = '';
            input.disabled = false;
            sendBtn.disabled = false;
            input.placeholder = 'พิมพ์ข้อความตอบลูกค้า...';
        } else {
            btnTakeover.style.display = '';
            btnHandback.style.display = 'none';
            input.disabled = true;
            sendBtn.disabled = true;
            input.placeholder = 'กด "Take Over" ก่อนจึงจะพิมพ์ได้';
        }
    },

    subscribeSession(sessionId) {
        if (!echo) return;

        this.echoChannel = echo.private(`chat.${sessionId}`)
            .listen('.NewChatMessage', (data) => {
                if ((data.sender_type || '').toLowerCase() !== 'admin') {
                    this.appendMessage({
                        id:          data.id,
                        sender_type: data.sender_type,
                        content:     data.content,
                        metadata:    data.metadata,
                        timestamp:   data.timestamp,
                    }, true);
                    playNotify();
                    this.loadSessions(); // refresh unread count
                }
            })
            .listen('.ChatHandoff', (data) => {
                this.currentHandledBy = data.handled_by;
                const s = this.sessions.find(x => x.id === sessionId);
                if (s) {
                    s.handled_by = data.handled_by;
                    this.renderSessions();
                }
                this.updateHeaderUI(sessionId, { handled_by: data.handled_by, status: 'active' });
            });
    },

    appendMessage(msg, animate) {
        if (msg.id && this.renderedIds.has(msg.id)) return;
        if (msg.id) this.renderedIds.add(msg.id);
        const msgEl = document.getElementById('chatMessages');
        const isSystem = msg.metadata?.system;

        if (isSystem) {
            const el = document.createElement('div');
            el.className = `msg-bubble--system ${animate ? 'msg-animate' : ''}`;
            el.textContent = msg.content;
            msgEl.appendChild(el);
            this.scrollToBottom();
            return;
        }

        const isUser  = msg.sender_type === 'user';
        const isAdmin = msg.sender_type === 'admin';
        const isAI    = msg.sender_type === 'ai';

        const row = document.createElement('div');
        const avatarClass = isUser ? 'msg-avatar--user' : isAdmin ? 'msg-avatar--admin' : 'msg-avatar--ai';
        const bubbleClass = isUser ? 'msg-bubble--user' : isAdmin ? 'msg-bubble--admin' : 'msg-bubble--ai';
        const avatarIcon  = isUser ? 'person' : isAdmin ? 'headset' : 'robot';
        const senderLabel = isUser ? 'ลูกค้า' : isAdmin ? (msg.sender_name ?? 'Admin') : 'AI';

        row.className = `msg-row msg-row--${msg.sender_type} ${animate ? 'msg-animate' : ''}`;

        const contentHtml = (msg.sender_type === 'ai' || msg.sender_type === 'admin')
            ? `<div class="msg-markdown">${renderMarkdown(msg.content)}</div>`
            : `<p style="margin:0;font-size:0.855rem">${(msg.content || '').replace(/\n/g, '<br>')}</p>`;

        row.innerHTML = isUser
            ? `<div class="msg-spacer"></div>
               <div class="msg-body">
                 <div class="msg-sender-label" style="text-align:right">${senderLabel}</div>
                 <div class="msg-bubble ${bubbleClass}">${contentHtml}</div>
                 <div class="msg-meta" style="text-align:right">${formatTime(msg.timestamp)}</div>
               </div>
               <div class="msg-avatar ${avatarClass}"><i class="bi bi-${avatarIcon}"></i></div>`
            : `<div class="msg-avatar ${avatarClass}"><i class="bi bi-${avatarIcon}"></i></div>
               <div class="msg-body">
                 <div class="msg-sender-label">${senderLabel}</div>
                 <div class="msg-bubble ${bubbleClass}">${contentHtml}</div>
                 <div class="msg-meta">${formatTime(msg.timestamp)}</div>
               </div>
               <div class="msg-spacer"></div>`;

        msgEl.appendChild(row);
        this.scrollToBottom();
    },

    scrollToBottom() {
        const el = document.getElementById('chatMessages');
        el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    },

    async send() {
        const input = document.getElementById('adminInput');
        const text  = input.value.trim();
        if (!text || !this.currentSessionId) return;

        input.value = '';
        input.style.height = 'auto';

        // Optimistic render
        this.appendMessage({
            sender_type: 'admin',
            content:     text,
            timestamp:   new Date().toISOString(),
        }, true);

        try {
            await csrfFetch(`/admin/chat/sessions/${this.currentSessionId}/send`, {
                method: 'POST',
                body: JSON.stringify({ message: text }),
            });
            this.loadSessions();
        } catch(e) {
            console.error('send error', e);
        }
    },

    async takeover() {
        if (!this.currentSessionId) return;
        try {
            const res = await csrfFetch(`/admin/chat/sessions/${this.currentSessionId}/takeover`, { method: 'POST' });
            const data = await res.json();
            this.currentHandledBy = 'admin';
            const s = this.sessions.find(x => x.id === this.currentSessionId);
            if (s) { s.handled_by = 'admin'; this.renderSessions(); }
            this.updateHeaderUI(this.currentSessionId, { handled_by: 'admin', status: 'active' });
        } catch(e) {
            console.error('takeover error', e);
        }
    },

    async handback() {
        if (!this.currentSessionId) return;
        try {
            const res = await csrfFetch(`/admin/chat/sessions/${this.currentSessionId}/handback`, { method: 'POST' });
            const data = await res.json();
            this.currentHandledBy = 'ai';
            const s = this.sessions.find(x => x.id === this.currentSessionId);
            if (s) { s.handled_by = 'ai'; this.renderSessions(); }
            this.updateHeaderUI(this.currentSessionId, { handled_by: 'ai', status: 'active' });
        } catch(e) {
            console.error('handback error', e);
        }
    },

    async resolve() {
        if (!this.currentSessionId) return;
        if (!confirm('ทำเครื่องหมาย session นี้ว่า Resolved?')) return;
        try {
            await csrfFetch(`/admin/chat/sessions/${this.currentSessionId}/resolve`, { method: 'POST' });
            const s = this.sessions.find(x => x.id === this.currentSessionId);
            if (s) { s.status = 'resolved'; this.renderSessions(); }
        } catch(e) {
            console.error('resolve error', e);
        }
    },
};

// ─── Init ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    AdminChat.loadSessions();

    // Auto-refresh sessions every 30 seconds
    setInterval(() => AdminChat.loadSessions(), 30000);

    // Admin input: Enter to send
    const input = document.getElementById('adminInput');
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            AdminChat.send();
        }
    });
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Configure marked
    if (typeof marked !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
    }
});
</script>

</body>
</html>
