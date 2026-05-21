<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Something went wrong') — {{ config('app.name', 'Behna Bazar') }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/brand/bb-mark.jpeg') }}">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(160deg, #f8fafc 0%, #eef2ff 100%);
            color: #0f172a;
            padding: 1.5rem;
        }
        .error-card {
            max-width: 28rem;
            width: 100%;
            text-align: center;
            background: #fff;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .error-code {
            font-size: 3rem;
            font-weight: 800;
            color: #6366f1;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        h1 { font-size: 1.25rem; margin: 0 0 0.75rem; }
        p { color: #64748b; font-size: 0.9375rem; line-height: 1.6; margin: 0 0 1.5rem; }
        .btn {
            display: inline-block;
            padding: 0.65rem 1.35rem;
            border-radius: 999px;
            background: #6366f1;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
        }
        .btn:hover { background: #4f46e5; color: #fff; }
    </style>
</head>
<body>
    <div class="error-card">
        @yield('content')
        <a href="{{ url('/') }}" class="btn">Back to home</a>
    </div>
</body>
</html>
