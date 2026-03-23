<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Evante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            background: #f5f2ee;
            font-family: 'Inter', sans-serif;
        }
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #1a2e35 0%, #1e3d44 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            padding: 3rem;
        }
        .login-left .brand {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .login-left .tagline {
            color: rgba(255,255,255,0.5);
            font-size: 1rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }
        .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
        }
        .login-card h4 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #1a2e35;
            margin-bottom: 0.25rem;
        }
        .login-card .subtitle {
            color: #6b8c93;
            font-size: 0.9rem;
            margin-bottom: 1.75rem;
        }
        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            color: #3d5a61;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .form-control {
            padding: 0.65rem 0.9rem;
            border-radius: 8px;
            border: 1px solid #e8e2d9;
        }
        .form-control:focus {
            border-color: #2A8B92;
            box-shadow: 0 0 0 3px rgba(42,139,146,0.15);
        }
        .btn-login {
            background: #2A8B92;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.7rem;
            font-weight: 600;
            font-size: 0.95rem;
            width: 100%;
            transition: all 0.2s ease;
        }
        .btn-login:hover { background: #1e6b71; color: #fff; box-shadow: 0 4px 12px rgba(42,139,146,0.35); }
        .input-group-text {
            background: #fff;
            border-radius: 8px 0 0 8px;
            border: 1px solid #e8e2d9;
            border-right: 0;
            color: #6b8c93;
        }
        .input-group .form-control {
            border-left: 0;
            border-radius: 0 8px 8px 0;
        }
        @media (max-width: 768px) {
            .login-left { display: none; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
    <div class="login-left d-none d-md-flex">
        <div class="text-center">
            <div class="brand"><i class="bi bi-building me-2"></i>Evante</div>
            <div class="tagline">Real Estate Management Platform</div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-card">
            <div class="d-md-none text-center mb-4">
                <h2 class="fw-bold"><i class="bi bi-building me-2"></i>Evante</h2>
            </div>

            <h4>Welcome back</h4>
            <p class="subtitle">Sign in to your account to continue</p>

            @if(session('success'))
                <div class="alert alert-success py-2 px-3" style="border-radius:10px; font-size:0.88rem;">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 px-3" style="border-radius:10px; font-size:0.88rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="small text-muted" style="text-decoration:none;">
                        Forgot password?
                    </a>
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                </button>
            </form>
        </div>
    </div>
</body>
</html>
