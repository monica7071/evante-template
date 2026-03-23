<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | Evante</title>
    @php $faviconLogo = optional(\App\Models\Company::query()->select('logo')->first())->logo; @endphp
    @if($faviconLogo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $faviconLogo) }}">
    @endif
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2A8B92;
            --primary-dark: #1e6b71;
            --primary-light: #3aa8b0;
            --primary-muted: rgba(42, 139, 146, 0.08);
            --cream: #F7EFE2;
            --cream-dark: #ede4d4;
            --text-dark: #1a2e35;
            --text-mid: #3d5a61;
            --text-light: #6b8c93;
            --surface: #ffffff;
            --bg: #f5f2ee;
            --border: #e8e2d9;
            --shadow-sm: 0 1px 3px rgba(42,139,146,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 16px rgba(42,139,146,0.12), 0 2px 8px rgba(0,0,0,0.06);
            --shadow-lg: 0 8px 32px rgba(42,139,146,0.15), 0 4px 16px rgba(0,0,0,0.08);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-lg: 16px;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
        }

        .layout-shell {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav-shell {
            padding-top: 20px;
            padding-bottom: 8px;
        }

        /* ── Top Navbar ── */
        #topnav {
            background: linear-gradient(135deg, #1a2e35 0%, #1e3d44 100%);
            box-shadow: 0 2px 20px rgba(0,0,0,0.25);
            padding: 0 28px;
            min-height: 62px;
            border-bottom: 1px solid rgba(42,139,146,0.3);
            border-radius: 100px;
            overflow: visible;
        }
        #topnav .navbar-brand {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0;
            margin-right: 28px;
        }
        #topnav .navbar-brand .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
        }
        #topnav .navbar-brand .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: inherit;
        }
        #topnav .navbar-brand:hover { color: #e2e8f0; }

        /* Nav links */
        #topnav .nav-link {
            color: rgba(255,255,255,.65);
            font-size: 13.5px;
            font-weight: 500;
            padding: 20px 13px !important;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
            white-space: nowrap;
        }
        #topnav .nav-link:hover {
            color: #fff;
        }
        #topnav .nav-link.active {
            color: #fff;
            border-bottom-color: var(--primary);
        }
        #topnav .dropdown-toggle::after {
            vertical-align: 0.18em;
        }

        /* Dropdown menu */
        #topnav .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 6px;
            min-width: 190px;
        }
        #topnav .dropdown-item {
            font-size: 13.5px;
            padding: 8px 12px;
            border-radius: 6px;
            color: var(--text-dark);
            transition: background .15s;
        }
        #topnav .dropdown-item:hover,
        #topnav .dropdown-item:focus {
            background: var(--primary-muted);
            color: var(--primary-dark);
        }
        #topnav .dropdown-item.active-item {
            background: var(--primary-muted);
            color: var(--primary-dark);
            font-weight: 600;
        }
        #topnav .dropdown-divider { margin: 4px 0; }

        /* Navbar toggler */
        #topnav .navbar-toggler {
            border-color: rgba(255,255,255,.3);
            padding: 4px 10px;
        }
        #topnav .navbar-toggler-icon {
            filter: invert(1);
            width: 1.2em; height: 1.2em;
        }

        /* ── Mobile + Tablet portrait (< 992px): hamburger menu ── */
        @media (max-width: 991.98px) {
            .nav-shell { padding-top: 12px; padding-bottom: 4px; }
            #topnav {
                border-radius: var(--radius);
                padding: 0 16px;
                min-height: auto;
            }
            #topnav .container-fluid { flex-wrap: wrap; }
            #topnav .navbar-brand { font-size: 1rem; margin-right: auto; padding: 12px 0; }
            #topnav .navbar-brand .brand-icon { width: 28px; height: 28px; }
            #topnav .navbar-toggler { margin: 10px 0; }

            /* Collapsed menu fills width below brand row */
            #topnav .navbar-collapse {
                border-top: 1px solid rgba(255,255,255,0.1);
                margin: 0 -16px;
                padding: 8px 16px 12px;
            }
            #topnav .nav-link {
                padding: 10px 8px !important;
                border-bottom: none;
                font-size: 14px;
            }
            #topnav .nav-link.active {
                background: rgba(42,139,146,0.2);
                border-radius: var(--radius-sm);
                border-bottom: none;
            }
            /* Dropdown menu inside collapsed nav */
            #topnav .dropdown-menu {
                background: rgba(255,255,255,0.06);
                border: none;
                box-shadow: none;
                padding: 0 0 0 16px;
            }
            #topnav .dropdown-item {
                color: rgba(255,255,255,0.7);
                font-size: 13px;
                padding: 8px 10px;
            }
            #topnav .dropdown-item:hover,
            #topnav .dropdown-item:focus {
                background: rgba(42,139,146,0.15);
                color: #fff;
            }
            #topnav .dropdown-item.active-item {
                background: rgba(42,139,146,0.2);
                color: #fff;
            }
            #topnav .dropdown-item i { color: rgba(255,255,255,0.4) !important; }

            /* User button on mobile/tablet portrait */
            #topnav .ms-lg-2 { margin-left: 0 !important; margin-top: 4px; }
            #topnav .ms-lg-2 button { width: 100%; justify-content: flex-start; }
        }

        /* ── Tablet landscape: compact nav (992px - 1199px) ── */
        @media (min-width: 992px) and (max-width: 1199.98px) {
            #topnav { padding: 0 18px; }
            #topnav .navbar-brand { margin-right: 14px; font-size: 1rem; }
            #topnav .nav-link {
                font-size: 12px !important;
                padding: 20px 8px !important;
            }
            #topnav .nav-link > i { display: none; }
            #topnav .dropdown-toggle::after { margin-left: 2px; }
            /* User button: avatar only */
            .user-btn { padding: 4px !important; border-radius: 50% !important; gap: 0 !important; width: 36px; height: 36px; justify-content: center; }
            .user-btn-name, .user-btn-role { display: none !important; }
            .user-btn::after { display: none !important; }
        }

        /* ── Page title bar ── */
        #page-title-bar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 12px 28px;
        }
        #page-title-bar h5 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        /* ── Main content ── */
        #main-content {
            flex: 1;
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 48px 60px;
        }

        @media (max-width: 1024px) {
            #main-content {
                padding: 36px 32px;
            }
        }

        @media (max-width: 640px) {
            #main-content {
                padding: 28px 20px;
            }
        }

        /* ── Page headings ── */
        #main-content > h2,
        #main-content > .page-header h3,
        #main-content .page-title {
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.02em;
        }

        /* ── Cards ── */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            background: var(--surface);
            transition: box-shadow 0.2s ease;
        }
        .card:hover {
            box-shadow: var(--shadow-md);
        }
        .card-header {
            background: var(--cream);
            border-bottom: 1px solid var(--border);
            border-radius: var(--radius) var(--radius) 0 0 !important;
            font-weight: 600;
            color: var(--text-dark);
            padding: 14px 20px;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #fff !important;
            border-radius: var(--radius-sm);
            font-weight: 500;
            letter-spacing: 0.01em;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
            box-shadow: 0 4px 12px rgba(42,139,146,0.35);
            transform: translateY(-1px);
        }
        .btn-outline-secondary {
            border-color: var(--border);
            color: var(--text-mid);
            border-radius: var(--radius-sm);
        }
        .btn-outline-secondary:hover {
            background: var(--cream);
            border-color: var(--primary);
            color: var(--primary);
        }
        .btn-outline-primary {
            border-color: rgba(42,139,146,0.3);
            color: var(--primary);
            border-radius: var(--radius-sm);
        }
        .btn-outline-primary:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .btn-outline-danger {
            border-radius: var(--radius-sm);
        }

        /* ── Tables ── */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }
        .table thead th {
            background: var(--cream);
            color: var(--text-mid);
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 2px solid var(--border);
            padding: 12px 16px;
        }
        .table tbody tr {
            transition: background 0.15s;
        }
        .table tbody tr:hover {
            background: var(--primary-muted);
        }
        .table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        /* ── Forms ── */
        .form-control, .form-select {
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 0.55rem 0.9rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(42,139,146,0.15);
            outline: none;
        }
        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-mid);
            margin-bottom: 6px;
        }

        /* ── Alerts ── */
        .alert-success {
            background: rgba(42,139,146,0.1);
            border-color: rgba(42,139,146,0.25);
            color: var(--primary-dark);
        }
    </style>
    @yield('styles')
</head>
<body>

    {{-- Impersonation Banner --}}
    @if(session('impersonating_as'))
    <div class="text-center py-2 px-4 fw-medium" style="background:#f59e0b; color:#1a2e35; font-size:0.85rem;">
        You are impersonating
        <strong>{{ auth()->user()->name }}</strong>
        ({{ auth()->user()->organization->name ?? '—' }}).
        <form method="POST" action="{{ route('super-admin.leave-impersonate') }}" class="d-inline ms-3">
            @csrf
            <button type="submit" class="btn btn-link p-0 text-decoration-underline fw-bold" style="color:#1a2e35; font-size:0.85rem;">
                Leave Impersonation
            </button>
        </form>
    </div>
    @endif

    {{-- ════════════ Top Navbar ════════════ --}}
    @php
        $isListing   = request()->routeIs('locations.*') || request()->routeIs('projects.*') || request()->routeIs('units.*');
        $isTemplate  = request()->routeIs('upload-template.*') || request()->routeIs('templates.*');
        $isEmployee  = request()->routeIs('employee.*');
        $companyLogoPath = optional(\App\Models\Company::query()->select('logo')->first())->logo;
    @endphp

    <div class="layout-shell nav-shell">
        <nav id="topnav" class="navbar navbar-expand-lg">
            <div class="container-fluid px-0">

            {{-- Brand --}}
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <span class="brand-icon">
                    @if(!empty($companyLogoPath))
                        <img src="{{ asset('storage/' . $companyLogoPath) }}" alt="Company Logo">
                    @else
                        <i class="bi bi-building text-white"></i>
                    @endif
                </span>
                Evante
            </a>

            {{-- Mobile toggle --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNavItems">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="topNavItems">
                {{-- Left nav --}}
                <ul class="navbar-nav align-items-lg-stretch me-auto">

                    {{-- Overview --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Overview
                        </a>
                    </li>

                    {{-- Buy/Sale --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('buy-sale.*') ? 'active' : '' }}"
                           href="{{ route('buy-sale.index') }}">
                            <i class="bi bi-shuffle me-1"></i> Buy/Sale
                        </a>
                    </li>

                    {{-- Listing Setting dropdown --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ $isListing ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Listing Setting
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('locations.*') ? 'active-item' : '' }}"
                                   href="{{ route('locations.index') }}">
                                    <i class="bi bi-geo-alt me-2 text-secondary"></i>Location
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('projects.*') ? 'active-item' : '' }}"
                                   href="{{ route('projects.index') }}">
                                    <i class="bi bi-buildings me-2 text-secondary"></i>Project
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('units.*') ? 'active-item' : '' }}"
                                   href="{{ route('units.index') }}">
                                    <i class="bi bi-door-open me-2 text-secondary"></i>Listing
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('units.import.*') ? 'active-item' : '' }}"
                                   href="{{ route('units.import.form') }}">
                                    <i class="bi bi-file-earmark-arrow-up me-2 text-secondary"></i>Import Excel
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Floor Plan --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('floor-plan.*') ? 'active' : '' }}"
                           href="{{ route('floor-plan.index') }}">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Floor Plan
                        </a>
                    </li>

                    {{-- Report --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('report.*') ? 'active' : '' }}"
                           href="{{ route('report.index') }}">
                            <i class="bi bi-graph-up-arrow me-1"></i> Report
                        </a>
                    </li>

                    {{-- Finance --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('finance.*') ? 'active' : '' }}"
                           href="{{ route('finance.index') }}">
                            <i class="bi bi-cash-coin me-1"></i> Finance
                        </a>
                    </li>

                    {{-- Activity --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}"
                           href="{{ route('activity.index') }}">
                            <i class="bi bi-lightning-charge me-1"></i> Activity
                        </a>
                    </li>

                </ul>

                {{-- Right: Template/Employee/User --}}
                @auth
                    <ul class="navbar-nav align-items-lg-center ms-auto gap-lg-2">
                        {{-- Template dropdown --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $isTemplate ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Template
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('upload-template.*') ? 'active-item' : '' }}"
                                       href="{{ route('upload-template.create') }}">
                                        <i class="bi bi-upload me-2 text-secondary"></i>Upload Template
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('templates.*') ? 'active-item' : '' }}"
                                       href="{{ route('templates.index') }}">
                                        <i class="bi bi-layout-text-window me-2 text-secondary"></i>Custom Template
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Employee dropdown --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $isEmployee ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Employee
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('employee.company.*') ? 'active-item' : '' }}"
                                       href="{{ route('employee.company.index') }}">
                                        <i class="bi bi-building-gear me-2 text-secondary"></i>Company Information
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('employee.profile-info.*') ? 'active-item' : '' }}"
                                       href="{{ route('employee.profile-info.index') }}">
                                        <i class="bi bi-card-list me-2 text-secondary"></i>Profile Information
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('employee.list.*') ? 'active-item' : '' }}"
                                       href="{{ route('employee.list.index') }}">
                                        <i class="bi bi-person-lines-fill me-2 text-secondary"></i>Employee
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('employee.positions.*') ? 'active-item' : '' }}"
                                       href="{{ route('employee.positions.index') }}">
                                        <i class="bi bi-diagram-3 me-2 text-secondary"></i>Position
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('employee.teams.*') ? 'active-item' : '' }}"
                                       href="{{ route('employee.teams.index') }}">
                                        <i class="bi bi-people-fill me-2 text-secondary"></i>Team
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- User dropdown --}}
                        <li class="nav-item dropdown ms-lg-2">
                            <button class="btn btn-sm d-flex align-items-center gap-2 dropdown-toggle user-btn"
                                    type="button" data-bs-toggle="dropdown"
                                    style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); border-radius:10px; color:#fff; padding:5px 12px;">
                                @if(Auth::user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt=""
                                         class="rounded-circle" style="width:26px;height:26px;object-fit:cover;">
                                @else
                                    <span class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                                          style="width:26px;height:26px;font-size:0.65rem;background:var(--primary);flex-shrink:0;">
                                        {{ Auth::user()->initials }}
                                    </span>
                                @endif
                                <span class="user-btn-name small fw-semibold">{{ Auth::user()->name }}</span>
                                <span class="user-btn-role badge"
                                      style="background:rgba(255,255,255,.15);font-size:0.6rem;font-weight:500;">
                                    {{ ucfirst(Auth::user()->role) }}
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end"
                                style="border-radius:10px; border:1px solid #e4e7ec; box-shadow:0 8px 24px rgba(0,0,0,0.1); min-width:200px;">
                                <li class="px-3 py-2 border-bottom">
                                    <div class="fw-semibold small text-dark">{{ Auth::user()->name }}</div>
                                    <div class="text-muted" style="font-size:0.72rem;">{{ Auth::user()->email }}</div>
                                </li>
                                @if(auth()->user()->isSuperAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('super-admin.dashboard') }}">
                                        <i class="bi bi-shield-lock me-2 text-secondary"></i>Super Admin
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="bi bi-person-circle me-2 text-secondary"></i>Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-left me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                @endauth
            </div>
        </nav>
    </div>

    {{-- ════════════ Page Title Bar ════════════ --}}
    <!--<div id="page-title-bar">
        <h5>@yield('title', 'Dashboard')</h5>
    </div>-->

    {{-- ════════════ Main Content ════════════ --}}
    <div class="layout-shell">
        <div id="main-content">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Preserve scroll position across page reloads --}}
    <script>
    (function() {
        const key = 'scrollPos_' + location.pathname;

        // Restore scroll position after page load
        const saved = sessionStorage.getItem(key);
        if (saved) {
            requestAnimationFrame(() => {
                window.scrollTo(0, parseInt(saved, 10));
                sessionStorage.removeItem(key);
            });
        }

        // Save scroll position before navigating away
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[href], button[type="submit"]');
            if (!link) return;

            // Only for same-origin navigation (not external links, not # anchors)
            if (link.tagName === 'A') {
                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;
            }

            sessionStorage.setItem(key, window.scrollY);
        });

        // Also save on form submit (for select onchange="this.form.submit()")
        document.addEventListener('submit', function() {
            sessionStorage.setItem(key, window.scrollY);
        });
    })();
    </script>

    @yield('scripts')
</body>
</html>
