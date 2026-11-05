<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $websiteSettings->nama_website }} - Masuk ke Sistem</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0F172A', // Slate 900
                        accent: '#2563EB',  // Blue 600
                        success: '#10B981', // Emerald 500
                    }
                }
            }
        }
    </script>
</head>
<body class="auth-shell font-poppins bg-slate-50 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Background Grid Pattern -->
    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(#2563EB_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>

    <!-- Main Login Card Container -->
    <div class="w-full max-w-md relative z-10">
        
        <!-- Top Back Link -->
        <div class="mb-6 flex justify-between items-center">
            <a href="{{ url('/') }}" class="inline-flex items-center text-xs font-semibold text-slate-500 hover:text-accent transition">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Beranda
            </a>
            <span class="text-xs text-slate-400 font-medium">KP Informatika</span>
        </div>

        <!-- Form Card -->
        <div class="auth-card bg-white rounded-3xl border border-slate-200 shadow-xl p-8 space-y-6">
            
            <!-- Brand Logo & Header -->
            <div class="text-center space-y-2">
                <div class="inline-flex items-center justify-center w-14 h-14 bg-accent text-white rounded-2xl shadow-lg shadow-accent/30 mb-2">
                    <img src="{{ $websiteSettings->logo_url }}" alt="" class="h-full w-full rounded-2xl bg-white object-contain" aria-hidden="true">
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Masuk ke {{ $websiteSettings->nama_website }}</h1>
                <p class="text-xs text-slate-500">Masukkan akun admin Anda untuk mengelola rental</p>
            </div>

            <!-- Form Inputs -->
            <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-4">
                
                <!-- Username / Email Input -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-700">Username atau Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-regular fa-user text-sm"></i>
                        </span>
                        <input type="text" id="username" required placeholder="admin@sewaride.com" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 focus:bg-white transition">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-semibold text-slate-700">Kata Sandi</label>
                        <a href="#" onclick="alert('Fitur lupa kata sandi dapat disesuaikan pada sistem backend.')" class="text-xs text-accent hover:underline font-medium">Lupa sandi?</a>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" id="password" required placeholder="••••••••" class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 focus:bg-white transition">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-regular fa-eye text-sm" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" id="remember" class="w-4 h-4 text-accent border-slate-300 rounded focus:ring-accent">
                    <label for="remember" class="text-xs text-slate-600 select-none">Ingat saya di perangkat ini</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 bg-accent hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition shadow-lg shadow-accent/30 flex items-center justify-center space-x-2">
                    <span>Masuk ke Dashboard</span>
                    <i class="fa-solid fa-right-to-bracket text-xs"></i>
                </button>
            </form>

            <!-- Footer Info Card -->
            <div class="pt-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-400">Aplikasi Sistem Pengelolaan Rental Kendaraan</p>
            </div>
        </div>

        <!-- Copyright Info -->
        <p class="text-center text-xs text-slate-400 mt-6">&copy; {{ date('Y') }} {{ $websiteSettings->nama_website }}. All rights reserved.</p>
    </div>

    <script>
        // Toggle Show/Hide Password
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Simulasi Login Alur Frontend
        function handleLogin(e) {
            e.preventDefault();
            // Arahkan langsung ke halaman dashboard admin
            window.location.href = "{{ url('/dashboard') }}";
        }
    </script>
</body>
</html>