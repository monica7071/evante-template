<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Link Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .error-card {
            max-width: 520px;
            width: 100%;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.15);
            padding: 36px 40px;
            text-align: center;
        }
        .error-icon {
            width: 72px;
            height: 72px;
            border-radius: 24px;
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 34px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        p {
            color: #4b5563;
            margin-bottom: 0;
            line-height: 1.5;
        }
        .mt-4 a {
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="error-card">
    <div class="error-icon">!</div>
    <h1>Signature Link Error</h1>
    <p>{{ $message ?? 'The signature link you followed is invalid or has expired. Please request a new link from the staff.' }}</p>
    <p class="mt-4">
        <a href="/" class="btn btn-dark w-100">Back to Home</a>
    </p>
</div>
</body>
</html>
