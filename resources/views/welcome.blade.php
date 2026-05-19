<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url={{ route('home') }}">
    <title>Behna Bazar</title>
    <script>window.location.replace(@json(route('home')));</script>
</head>
<body>
    <p>Redirecting to <a href="{{ route('home') }}">Behna Bazar</a>…</p>
</body>
</html>
