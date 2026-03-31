<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | BeHome</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            background: radial-gradient(ellipse at 70% 0%, #1e1b4b 0%, #0f172a 60%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="p-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-purple-400 mb-2">
                BeHome Admin
            </h1>
            <p class="text-slate-400">Welcome back! Please login to your account.</p>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-3xl p-10">
            <form action="{{ route('admin.login.post') }}" method="POST" class="w-full">
                @csrf
                
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-5 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/40 focus:bg-white/20 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition"
                               placeholder="name@example.com">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                        </div>
                        <input type="password" id="password" name="password" required
                               class="w-full px-5 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/40 focus:bg-white/20 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 outline-none transition"
                               placeholder="••••••••">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" 
                               class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-indigo-600 focus:ring-indigo-600 focus:ring-offset-slate-900">
                        <label for="remember" class="ml-2 block text-sm text-slate-400">Remember me</label>
                    </div>

                    <button type="submit" class="admin-btn-primary w-full">
                        Sign In
                    </button>
                    
                    <div class="text-center pt-4">
                        <p class="text-slate-500 text-xs">
                            Secure connection enabled. All sessions are monitored.
                        </p>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer Info -->
        <p class="text-center mt-10 text-slate-500 text-sm">
            &copy; {{ date('Y') }} BeHome Inc. All rights reserved.
        </p>
    </div>
</body>
</html>