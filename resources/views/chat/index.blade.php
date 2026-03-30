<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Evante AI Chat</title>

    {{-- CDN: Bootstrap 5, Bootstrap Icons, Google Fonts, marked.js --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked@9.1.6/marked.min.js"></script>

    {{-- Vite compiled app.css only (chat.js is always inlined below) --}}
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif

    {{-- Laravel Echo + Pusher via CDN for real-time --}}
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <style>
        /* ── Reset & Root ──────────────────────────────────────────── */
        :root {
            --primary:       #2A8B92;
            --primary-dark:  #1e6b71;
            --primary-light: #3aa8b0;
            --primary-muted: rgba(42,139,146,0.1);
            --cream:         #F7EFE2;
            --cream-dark:    #ede4d4;
            --text-dark:     #1a2e35;
            --text-mid:      #3d5a61;
            --text-light:    #6b8c93;
            --surface:       #ffffff;
            --bg:            #f5f2ee;
            --border:        #e8e2d9;
            --sidebar-bg:    #1a2e35;
            --sidebar-hover: rgba(255,255,255,0.07);
            --shadow-sm:     0 1px 3px rgba(42,139,146,0.08),0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:     0 4px 16px rgba(42,139,146,0.12),0 2px 8px rgba(0,0,0,0.06);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
        }

        /* ── Layout Shell ──────────────────────────────────────────── */
        .chat-layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ── Sidebar ───────────────────────────────────────────────── */
        .chat-sidebar {
            width: 280px;
            flex-shrink: 0;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-head {
            padding: 20px 16px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #fff;
            font-weight: 700;
            flex: 1;
            letter-spacing: 0.3px;
        }

        .sidebar-brand span {
            display: block;
            font-family: 'Inter', sans-serif;
            font-size: 0.65rem;
            font-weight: 400;
            color: rgba(255,255,255,0.45);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-top: 1px;
        }

        .btn-new-chat {
            background: var(--primary);
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .btn-new-chat:hover { background: var(--primary-dark); }

        .sidebar-section-label {
            padding: 12px 16px 6px;
            font-size: 0.65rem;
            font-weight: 600;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .session-list {
            flex: 1;
            overflow-y: auto;
            padding: 4px 8px 16px;
        }
        .session-list::-webkit-scrollbar { width: 3px; }
        .session-list::-webkit-scrollbar-track { background: transparent; }
        .session-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 99px; }

        .session-item {
            width: 100%;
            background: none;
            border: none;
            color: rgba(255,255,255,0.7);
            border-radius: 10px;
            padding: 10px 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            text-align: left;
            margin-bottom: 2px;
        }
        .session-item:hover { background: var(--sidebar-hover); color: #fff; }
        .session-item.active { background: var(--primary-muted); color: #fff; }

        .session-icon {
            color: var(--primary-light);
            font-size: 1rem;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .session-info { flex: 1; min-width: 0; }
        .session-title {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .session-preview {
            display: block;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.4);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }
        .session-time {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.3);
            white-space: nowrap;
            margin-top: 2px;
        }
        .session-empty {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.3);
            text-align: center;
            padding: 24px 12px;
        }

        .sidebar-footer {
            padding: 12px 16px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.45);
            font-size: 0.78rem;
            text-decoration: none;
            padding: 6px 4px;
            border-radius: 8px;
            transition: color 0.2s, background 0.2s;
        }
        .sidebar-footer a:hover { color: #fff; background: var(--sidebar-hover); }

        /* ── Overlay (mobile) ──────────────────────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }
        .sidebar-overlay.visible { display: block; }

        /* ── Main Area ─────────────────────────────────────────────── */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
        }

        /* ── Header ────────────────────────────────────────────────── */
        .chat-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 20px;
            height: 64px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }

        .btn-sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-mid);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: background 0.15s;
        }
        .btn-sidebar-toggle:hover { background: var(--primary-muted); }

        .chat-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(42,139,146,0.3);
        }

        .chat-header-info { flex: 1; }
        .chat-header-info h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }
        .chat-header-info p {
            font-size: 0.72rem;
            color: var(--text-light);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #22c55e;
            animation: pulse-dot 2s infinite;
        }
        @keyframes pulse-dot {
            0%,100% { opacity: 1; }
            50%      { opacity: 0.4; }
        }

        .chat-header-actions { display: flex; gap: 6px; }
        .header-btn {
            background: none;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 7px 10px;
            color: var(--text-mid);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .header-btn:hover { background: var(--primary-muted); border-color: var(--primary); color: var(--primary); }

        /* ── Messages Area ─────────────────────────────────────────── */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            background: var(--bg);
        }
        .chat-messages::-webkit-scrollbar { width: 4px; }
        .chat-messages::-webkit-scrollbar-track { background: transparent; }
        .chat-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }
        .chat-messages.drag-over { background: rgba(42,139,146,0.05); outline: 2px dashed var(--primary); outline-offset: -8px; border-radius: 12px; }

        /* ── Message Row ───────────────────────────────────────────── */
        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            max-width: 100%;
        }
        .msg-row--user { flex-direction: row; }
        .msg-row--bot  { flex-direction: row; }
        .msg-spacer    { flex: 1; min-width: 32px; max-width: 80px; }

        .msg-animate { animation: msg-in 0.25s ease; }
        @keyframes msg-in {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Avatars */
        .msg-avatar {
            width: 32px;
            height: 32px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            margin-bottom: 4px;
        }
        .msg-avatar--user {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            font-size: 0.9rem;
        }
        .msg-avatar--bot { background: transparent; }

        /* Bubble */
        .msg-body { display: flex; flex-direction: column; max-width: min(640px, 76%); }
        .msg-row--user .msg-body { align-items: flex-end; }
        .msg-row--bot  .msg-body { align-items: flex-start; }

        .msg-bubble {
            padding: 10px 14px;
            border-radius: 16px;
            line-height: 1.55;
            word-break: break-word;
            box-shadow: var(--shadow-sm);
        }
        .msg-bubble--user {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .msg-bubble--bot {
            background: var(--surface);
            color: var(--text-dark);
            border-bottom-left-radius: 4px;
            border: 1px solid var(--border);
        }

        .msg-text { font-size: 0.88rem; margin: 0; }

        .msg-meta {
            font-size: 0.66rem;
            color: var(--text-light);
            margin-top: 3px;
            padding: 0 2px;
        }

        /* Image */
        .msg-image {
            max-width: 240px;
            max-height: 200px;
            border-radius: 10px;
            display: block;
            margin-bottom: 4px;
            object-fit: cover;
        }
        .msg-file-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: inherit;
            text-decoration: underline;
        }

        /* Markdown inside bot bubble */
        .msg-markdown { font-size: 0.875rem; }
        .msg-markdown p  { margin: 0 0 8px; line-height: 1.6; }
        .msg-markdown p:last-child { margin-bottom: 0; }
        .msg-markdown strong { font-weight: 600; color: var(--text-dark); }
        .msg-markdown em     { font-style: italic; }
        .msg-markdown code {
            background: var(--cream-dark);
            border-radius: 4px;
            padding: 1px 5px;
            font-size: 0.82rem;
            font-family: 'JetBrains Mono', monospace;
        }
        .msg-markdown blockquote {
            border-left: 3px solid var(--primary);
            margin: 8px 0;
            padding: 4px 12px;
            color: var(--text-mid);
            background: var(--primary-muted);
            border-radius: 0 8px 8px 0;
        }
        .msg-markdown h1,.msg-markdown h2,.msg-markdown h3 {
            font-size: 0.95rem;
            font-weight: 700;
            margin: 10px 0 6px;
            color: var(--text-dark);
        }
        .msg-markdown ul,.msg-markdown ol { padding-left: 18px; margin: 6px 0; }
        .msg-markdown li { margin-bottom: 3px; line-height: 1.5; }
        .msg-markdown img,
        .msg-markdown-img {
            max-width: 100%;
            max-height: 260px;
            border-radius: 10px;
            margin: 8px 0 4px;
            display: block;
            object-fit: cover;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .msg-markdown img:hover,
        .msg-markdown-img:hover { opacity: 0.88; }
        .msg-markdown a { color: var(--primary); text-decoration: underline; }

        /* Tables inside markdown */
        .chat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            margin: 8px 0;
            border-radius: 8px;
            overflow: hidden;
        }
        .chat-table th {
            background: var(--primary-muted);
            color: var(--primary-dark);
            padding: 7px 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }
        .chat-table td {
            padding: 6px 10px;
            border-bottom: 1px solid var(--border);
            color: var(--text-dark);
        }
        .chat-table tr:last-child td { border-bottom: none; }
        .chat-table tr:nth-child(even) td { background: rgba(0,0,0,0.015); }

        /* Typing indicator */
        .typing-bubble {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 16px;
        }
        .typing-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
            animation: typing-anim 1.2s infinite ease-in-out;
        }
        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing-anim {
            0%,60%,100% { transform: translateY(0); opacity: 0.5; }
            30%          { transform: translateY(-5px); opacity: 1; }
        }

        /* ── Quick Replies Bar ─────────────────────────────────────── */
        .quick-replies-bar {
            padding: 8px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            background: var(--bg);
            border-top: 1px solid var(--border);
        }
        .quick-replies-bar.hidden { display: none; }

        .qr-btn {
            background: var(--surface);
            border: 1.5px solid var(--primary);
            color: var(--primary-dark);
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .qr-btn:hover {
            background: var(--primary);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(42,139,146,0.25);
        }

        /* ── File Preview Bar ──────────────────────────────────────── */
        .file-preview {
            background: var(--cream);
            border-top: 1px solid var(--border);
            padding: 10px 16px;
        }
        .file-preview.hidden { display: none; }
        .file-preview-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 360px;
        }
        .preview-thumb {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid var(--border);
        }
        .preview-icon {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            background: var(--primary-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
        }
        .preview-info { flex: 1; min-width: 0; }
        .preview-name {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .preview-size {
            display: block;
            font-size: 0.7rem;
            color: var(--text-light);
        }
        .preview-remove {
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.1rem;
            cursor: pointer;
            flex-shrink: 0;
            opacity: 0.7;
            transition: opacity 0.15s;
        }
        .preview-remove:hover { opacity: 1; }

        /* ── Input Area ────────────────────────────────────────────── */
        .chat-input-area {
            background: var(--surface);
            border-top: 1px solid var(--border);
            padding: 12px 16px;
            flex-shrink: 0;
        }

        .input-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            background: var(--bg);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 8px 8px 8px 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-wrapper:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(42,139,146,0.1);
        }

        .message-input {
            flex: 1;
            background: none;
            border: none;
            outline: none;
            resize: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
            color: var(--text-dark);
            line-height: 1.5;
            max-height: 120px;
            overflow-y: auto;
            padding: 2px 0;
        }
        .message-input::placeholder { color: var(--text-light); }

        .input-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .input-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            border: none;
            background: none;
            color: var(--text-light);
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        .input-btn:hover { background: var(--primary-muted); color: var(--primary); }

        .btn-send {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff;
            font-size: 0.95rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
            box-shadow: 0 2px 8px rgba(42,139,146,0.3);
            flex-shrink: 0;
        }
        .btn-send:hover { transform: scale(1.06); box-shadow: 0 4px 12px rgba(42,139,146,0.4); }
        .btn-send:active { transform: scale(0.97); }

        .input-hint {
            text-align: right;
            font-size: 0.67rem;
            color: var(--text-light);
            margin-top: 6px;
            padding: 0 4px;
        }

        /* ── Empty State ───────────────────────────────────────────── */
        .chat-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 32px;
            gap: 12px;
        }
        .empty-icon {
            font-size: 3.5rem;
            line-height: 1;
        }
        .empty-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        .empty-sub {
            font-size: 0.82rem;
            color: var(--text-light);
            max-width: 280px;
        }

        /* ── Responsive ────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .chat-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                transform: translateX(-100%);
            }
            .chat-sidebar.open { transform: translateX(0); box-shadow: var(--shadow-md); }
            .btn-sidebar-toggle { display: flex; }
            .header-btn span { display: none; }
            .input-hint { display: none; }
        }

        /* ── Dark Mode ─────────────────────────────────────────────── */
        @media (prefers-color-scheme: dark) {
            :root {
                --surface:   #1e2d33;
                --bg:        #17252b;
                --border:    #2d4249;
                --text-dark: #e8f0f2;
                --text-mid:  #92b4bc;
                --text-light:#5d8690;
                --cream:     #1e2d33;
                --cream-dark:#253740;
                --sidebar-bg:#111e23;
            }
            .msg-bubble--bot {
                background: #1e2d33;
                border-color: #2d4249;
                color: #e8f0f2;
            }
            .chat-table th { color: #7ecdd4; }
            .chat-table td { color: #c8dde1; }
            .input-wrapper { background: #1a272d; }
        }
    </style>
</head>
<body>

<div class="chat-layout">

    {{-- ─── Sidebar ─────────────────────────────────────────────── --}}
    <aside class="chat-sidebar" id="chatSidebar">

        <div class="sidebar-head">
            <div class="sidebar-brand">
                Evante
                <span>Property Assistant</span>
            </div>
            <button class="btn-new-chat" id="newChatBtn" type="button">
                <i class="bi bi-plus-lg"></i> ใหม่
            </button>
        </div>

        <div class="sidebar-section-label">การสนทนา</div>

        <div class="session-list" id="sessionList">
            <p class="session-empty">ยังไม่มีประวัติการสนทนา</p>
        </div>

        <div class="sidebar-footer">
            <a href="{{ url('/dashboard') }}">
                <i class="bi bi-grid-1x2"></i> กลับหน้าหลัก
            </a>
            <a href="#" onclick="window.chatManager?.newChat(); return false;">
                <i class="bi bi-trash3"></i> ล้างประวัติแชท
            </a>
        </div>

    </aside>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ─── Main Area ───────────────────────────────────────────── --}}
    <main class="chat-main">

        {{-- Header --}}
        <div class="chat-header">
            <button class="btn-sidebar-toggle" id="sidebarToggle" type="button">
                <i class="bi bi-list"></i>
            </button>

            <div class="chat-header-avatar">🤖</div>

            <div class="chat-header-info">
                <h6>Evante AI Assistant</h6>
                <p>
                    <span class="status-dot"></span>
                    ออนไลน์ · ตอบด้วย AI
                    @if(!config('ai.anthropic.api_key'))
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;border-radius:6px;padding:2px 6px;">Mock Mode</span>
                    @endif
                </p>
            </div>

            <div class="chat-header-actions ms-auto">
                <button class="header-btn" type="button" onclick="window.chatManager?.newChat()" title="เริ่มแชทใหม่">
                    <i class="bi bi-plus-circle"></i>
                    <span>ใหม่</span>
                </button>
                <a href="{{ url('/dashboard') }}" class="header-btn" title="กลับหน้าหลัก">
                    <i class="bi bi-house"></i>
                    <span>หน้าหลัก</span>
                </a>
            </div>
        </div>

        {{-- Messages --}}
        <div class="chat-messages" id="chatMessages">
            {{-- Messages are injected by JS --}}
        </div>

        {{-- Quick Replies --}}
        <div class="quick-replies-bar hidden" id="quickReplies"></div>

        {{-- File Preview --}}
        <div class="file-preview hidden" id="filePreview"></div>

        {{-- Input Area --}}
        <div class="chat-input-area">
            <div class="input-wrapper">
                <textarea
                    class="message-input"
                    id="messageInput"
                    rows="1"
                    placeholder="พิมพ์ข้อความ... (Enter เพื่อส่ง, Shift+Enter ขึ้นบรรทัด)"
                    maxlength="2000"
                ></textarea>

                <div class="input-actions">
                    <input type="file" id="fileInput" class="d-none"
                           accept="image/*,.pdf,.doc,.docx">
                    <button class="input-btn" type="button"
                            onclick="document.getElementById('fileInput').click()"
                            title="แนบไฟล์/รูปภาพ">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <button class="btn-send" id="sendBtn" type="button" title="ส่งข้อความ">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </div>
            <div class="input-hint">
                <span id="charCount">0</span>/2000 ตัวอักษร &nbsp;·&nbsp; Enter เพื่อส่ง
            </div>
        </div>

    </main>
</div>

{{-- Chat JS: always inline (chat.js is not in the Vite manifest) --}}
<script type="module">
{!! file_get_contents(resource_path('js/chat.js')) !!}
</script>

{{-- Auto-resize textarea --}}
<script>
(function () {
    const ta = document.getElementById('messageInput');
    if (!ta) return;
    ta.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Configure marked options
    if (typeof marked !== 'undefined') {
        marked.setOptions({ breaks: true, gfm: true });
    }
})();
</script>

{{-- Admin message polling + optional Echo real-time --}}
<script>
(function () {
    // ── Shared state ──
    const seenIds = new Set();
    let echoInstance = null;

    // ── Helper: render an admin message on screen ──
    function handleAdminMessage(mgr, data) {
        const msgId = data.id;
        if (msgId && seenIds.has(msgId)) return;
        if (msgId) seenIds.add(msgId);
        mgr.hideTyping();
        const ts = data.timestamp || data.created_at || new Date().toISOString();
        mgr.renderMessage('bot', data.content, null, [], ts, true);
        mgr.saveToHistory('bot', data.content, null, ts);
        const info = document.querySelector('.chat-header-info p');
        if (info) info.innerHTML = '<span class="status-dot"></span> Admin กำลังช่วยเหลือ';
    }

    // ── 1. Init Echo (optional — polling works without it) ──
    try {
        const reverbKey = '{{ env("REVERB_APP_KEY") }}';
        if (typeof Echo !== 'undefined' && typeof Pusher !== 'undefined' && reverbKey) {
            echoInstance = new Echo({
                broadcaster: 'pusher',
                key: reverbKey,
                wsHost: '{{ env("REVERB_HOST", "localhost") }}',
                wsPort: {{ env("REVERB_PORT", 8080) }},
                wssPort: {{ env("REVERB_PORT", 8080) }},
                forceTLS: '{{ env("REVERB_SCHEME", "http") }}' === 'https',
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1',
                disableStats: true,
            });
            console.log('[Chat] Echo/Reverb connected');
        }
    } catch (e) { console.warn('[Chat] Echo init failed:', e.message); }

    // ── 2. Wait for chatManager, then start everything ──
    function boot() {
        const mgr = window.chatManager;
        if (!mgr || !mgr.sessionId) { setTimeout(boot, 300); return; }

        console.log('[Chat] Ready. sessionId =', mgr.sessionId);

        // ── 2a. Polling loop (primary delivery — always works) ──
        let lastSeen = new Date().toISOString();

        async function poll() {
            try {
                const res = await fetch(`/chat/sessions/${encodeURIComponent(mgr.sessionId)}/messages?since=${encodeURIComponent(lastSeen)}`);
                if (!res.ok) return;
                const data = await res.json();
                const msgs = (data.messages || []).sort((a, b) =>
                    new Date(a.timestamp || a.created_at || 0) - new Date(b.timestamp || b.created_at || 0)
                );
                for (const msg of msgs) {
                    const ts = msg.timestamp || msg.created_at;
                    if (ts && ts > lastSeen) lastSeen = ts;
                    if ((msg.sender_type || '').toLowerCase() === 'admin') {
                        handleAdminMessage(mgr, msg);
                    }
                }
            } catch (_) {}
        }

        poll();
        setInterval(poll, 3000);

        // ── 2b. Echo listener (bonus — instant delivery when Reverb works) ──
        if (echoInstance) {
            try {
                const ch = echoInstance.channel(`chat.${mgr.sessionId}`);
                if (ch) {
                    ch.listen('.MessageSent', (data) => {
                        console.log('[Echo] Received:', data.sender_type);
                        if (data.sender_type === 'admin' && data.content) {
                            handleAdminMessage(mgr, data);
                        }
                    });
                    console.log('[Echo] Subscribed to chat.' + mgr.sessionId);
                }
            } catch (e) { console.warn('[Echo] Subscribe failed:', e.message); }
        }

        // ── 2c. Override fetchResponse for admin-mode detection ──
        mgr.fetchResponse = async function (message, imageUrl) {
            const sendRequest = async () => {
                return await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message, image_url: imageUrl, session_id: this.sessionId }),
                });
            };

            let res = await sendRequest();

            // Auto-refresh CSRF token on 419 and retry once
            if (res.status === 419) {
                try {
                    const tokenRes = await fetch('/chat/csrf-token');
                    const tokenData = await tokenRes.json();
                    document.querySelector('meta[name="csrf-token"]').content = tokenData.token;
                    res = await sendRequest();
                } catch (e) {
                    console.warn('[CSRF] Refresh failed:', e.message);
                }
            }

            if (!res.ok) {
                this.hideTyping();
                this.renderMessage('bot', '⚠️ เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้งครับ', null, ['ลองใหม่'], null, true);
                return;
            }

            const data = await res.json();

            // Subscribe Echo on numeric db session id too (for status events)
            if (echoInstance && data.db_session_id) {
                try {
                    const ch2 = echoInstance.channel(`chat.${data.db_session_id}`);
                    if (ch2) {
                        ch2.listen('.MessageSent', (d) => {
                            if (d.sender_type === 'admin' && d.content) handleAdminMessage(mgr, d);
                        });
                    }
                } catch (_) {}
            }

            if (data.source === 'admin_mode') { this.hideTyping(); return; }

            this.hideTyping();
            this.renderMessage('bot', data.message, null, data.quick_replies ?? [], data.timestamp, true);
            this.saveToHistory('bot', data.message, null, data.timestamp);
            if (data.quick_replies?.length) this.showQuickReplies(data.quick_replies);
        };

        // ── 2d. Override submitMessage ──
        mgr.submitMessage = async function () {
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

            this.renderMessage('user', text, imageUrl, [], null, true);
            this.saveToHistory('user', text, imageUrl);
            this.showTyping();
            await mgr.fetchResponse(text, imageUrl);
        };
    }

    boot();
})();
</script>

</body>
</html>
