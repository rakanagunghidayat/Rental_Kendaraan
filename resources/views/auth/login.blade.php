<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ $websiteSettings->nama_website }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}">
</head>
<body class="auth-shell bg-slate-50 flex items-center justify-center min-h-screen p-4">
    <div class="auth-card w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-100">
        <div class="auth-brand-mark mx-auto mb-5 w-14 h-14 rounded-2xl bg-blue-50 text-accent flex items-center justify-center text-xl">
            <img src="{{ $websiteSettings->logo_url }}" alt="" class="h-full w-full rounded-2xl bg-white object-contain" aria-hidden="true">
        </div>
        <h2 class="text-2xl font-bold text-slate-900 text-center mb-2">{{ $websiteSettings->nama_website }}</h2>
        <p class="text-sm text-slate-500 text-center mb-6">Silakan masuk menggunakan akun admin</p>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-accent mb-5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke beranda
        </a>

        <!-- Pesan Error -->
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ url('/login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>