<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Login') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        body { font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f1f5f9; }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-lg p-6">
            @yield('content')
        </div>
    </div>
</body>
</html>