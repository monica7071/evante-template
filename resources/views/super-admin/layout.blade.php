<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | Super Admin | Evante</title>
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
            --radius: 12px;
            --radius-sm: 8px;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
        }

        /* ── Navbar ── */
        .sa-navbar {
            background: linear-gradient(135deg, #1a2e35 0%, #1e3d44 100%);
            box-shadow: 0 2px 20px rgba(0,0,0,0.25);
            padding: 0 28px;
            min-height: 62px;
            border-bottom: 1px solid rgba(42,139,146,0.3);
        }
        .sa-navbar .navbar-brand {
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.15rem;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sa-navbar .brand-icon {
            width: 32px; height: 32px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
        }
        .sa-badge {
            background: #f59e0b;
            color: #1a2e35;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .sa-navbar .nav-link {
            color: rgba(255,255,255,.65);
            font-size: 13.5px;
            font-weight: 500;
            padding: 20px 13px !important;
            border-bottom: 2px solid transparent;
            transition: color .2s, border-color .2s;
            white-space: nowrap;
        }
        .sa-navbar .nav-link:hover { color: #fff; }
        .sa-navbar .nav-link.active {
            color: #fff;
            border-bottom-color: var(--primary);
        }
        .sa-navbar .navbar-toggler {
            border-color: rgba(255,255,255,.3);
            padding: 4px 10px;
        }
        .sa-navbar .navbar-toggler-icon {
            filter: invert(1);
            width: 1.2em; height: 1.2em;
        }

        @media (max-width: 991.98px) {
            .sa-navbar { padding: 0 16px; min-height: auto; }
            .sa-navbar .navbar-brand { font-size: 1rem; padding: 12px 0; }
            .sa-navbar .navbar-collapse {
                border-top: 1px solid rgba(255,255,255,0.1);
                padding: 8px 0 12px;
            }
            .sa-navbar .nav-link {
                padding: 10px 8px !important;
                border-bottom: none;
                font-size: 14px;
            }
            .sa-navbar .nav-link.active {
                background: rgba(42,139,146,0.2);
                border-radius: var(--radius-sm);
                border-bottom: none;
            }
        }

        /* ── Content ── */
        .sa-content {
            flex: 1;
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 36px 48px;
        }
        @media (max-width: 1024px) { .sa-content { padding: 28px 24px; } }
        @media (max-width: 640px)  { .sa-content { padding: 20px 16px; } }

        /* ── Cards ── */
        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            background: var(--surface);
            transition: box-shadow 0.2s ease;
        }
        .card:hover { box-shadow: var(--shadow-md); }

        /* ── Tables ── */
        .table { border-collapse: separate; border-spacing: 0; }
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
        .table tbody tr { transition: background 0.15s; }
        .table tbody tr:hover { background: var(--primary-muted); }
        .table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: 0.88rem;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #fff !important;
            border-radius: var(--radius-sm);
            font-weight: 500;
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

        /* ── Badges ── */
        .badge-active   { background: rgba(16,185,129,0.1); color: #059669; font-weight: 600; }
        .badge-inactive { background: rgba(239,68,68,0.1);  color: #dc2626; font-weight: 600; }
        .badge-role-super_admin { background: rgba(139,92,246,0.1);  color: #7c3aed; }
        .badge-role-admin       { background: rgba(59,130,246,0.1);  color: #2563eb; }
        .badge-role-leader      { background: rgba(245,158,11,0.1);  color: #d97706; }
        .badge-role-agent       { background: rgba(42,139,146,0.1);  color: #2A8B92; }

        /* ── KPI cards ── */
        .kpi-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1.25rem;
            transition: all 0.2s;
        }
        .kpi-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
        .kpi-card-title {
            font-size: 0.72rem; font-weight: 700; color: var(--text-light);
            text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.4rem;
        }
        .kpi-card-value { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); line-height: 1.1; }
        .kpi-card-sub { font-size: 0.78rem; color: var(--text-light); margin-top: 0.3rem; }

        /* ── Storage bar ── */
        .storage-bar-wrap {
            height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;
        }
        .storage-bar-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }

        /* ── Empty state ── */
        .empty-state {
            text-align: center; padding: 2.5rem; color: var(--text-light); font-size: 0.88rem;
        }
        .empty-state i { font-size: 1.8rem; display: block; margin-bottom: 0.5rem; }

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

    {{-- ════════════ Navbar ════════════ --}}
    <nav class="sa-navbar navbar navbar-expand-lg">
        <div class="container-fluid px-0">
            <a class="navbar-brand" href="{{ route('super-admin.dashboard') }}">
                <span class="brand-icon">
                    <i class="bi bi-shield-lock text-white"></i>
                </span>
                Evante
                <span class="sa-badge">Super Admin</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#saNavItems">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="saNavItems">
                <ul class="navbar-nav me-auto ms-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('super-admin.dashboard') ? 'active' : '' }}"
                           href="{{ route('super-admin.dashboard') }}">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('super-admin.plans.*') ? 'active' : '' }}"
                           href="{{ route('super-admin.plans.index') }}">
                            <i class="bi bi-box-seam me-1"></i> Plans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('super-admin.organizations.*') ? 'active' : '' }}"
                           href="{{ route('super-admin.organizations.index') }}">
                            <i class="bi bi-buildings me-1"></i> Organizations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('super-admin.users.*') ? 'active' : '' }}"
                           href="{{ route('super-admin.users.index') }}">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bi bi-arrow-left me-1"></i> Back to App
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link" style="color:rgba(255,255,255,.5); cursor:default;">
                            <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name }}
                        </span>
                    </li>
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link p-0 text-decoration-none"
                                    style="color:rgba(255,255,255,.65); font-size:13.5px; font-weight:500; padding:20px 13px !important;">
                                <i class="bi bi-box-arrow-left me-1"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    {{-- ════════════ Content ════════════ --}}
    <div class="sa-content">

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

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show mb-4">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
