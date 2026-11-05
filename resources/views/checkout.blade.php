<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $websiteSettings->nama_website }} - Formulir Pemesanan Kendaraan</title>
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
                        primary: '#0F172A',
                        accent: '#2563EB',
                        success: '#10B981',
                    }
                }
            }
        }
    </script>
</head>
<body class="font-poppins bg-slate-50 text-slate-800 antialiased">

    <!-- Navbar Sederhana -->
    <header class="bg-white border-b border-slate-200 h-20 flex items-center justify-between px-6 lg:px-10 sticky top-0 z-50">
        <div class="flex items-center space-x-3">
            <div class="bg-accent text-white p-2.5 rounded-xl shadow-lg shadow-accent/30">
                @if($websiteSettings->logo_url)
                    <img src="{{ $websiteSettings->logo_url }}" alt="" class="checkout-brand-logo">
                @else
                    <i class="fa-solid fa-car-side text-xl"></i>
                @endif
            </div>
            <div>
                <h1 class="text-slate-900 font-bold text-lg tracking-wide">{{ $websiteSettings->nama_website }}</h1>
                <p class="text-xs text-slate-500">Formulir Penyewaan Cepat</p>
            </div>
        </div>
        <a href="{{ url('/') }}" class="text-sm font-medium text-slate-600 hover:text-accent transition flex items-center space-x-2">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </header>

    <!-- Main Container Formulir -->
    <main class="max-w-4xl mx-auto px-6 py-12">
        <div class="checkout-panel bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
            <!-- Header Card -->
            <div class="checkout-heading bg-primary p-8 text-white space-y-2">
                <span class="px-3 py-1 bg-accent/30 text-blue-300 rounded-full text-xs font-semibold uppercase tracking-wider inline-block border border-accent/40">
                    <i class="fa-solid fa-file-invoice mr-1"></i> Form Pemesanan
                </span>
                <h2 class="text-2xl font-bold">Lengkapi Data Diri Anda</h2>
                <p class="text-slate-300 text-sm">Silakan isi data di bawah dengan benar untuk konfirmasi penyewaan kendaraan.</p>
            </div>

            <!-- Form Body -->
            <form action="{{ route('checkout.store', $kendaraan) }}" method="POST" enctype="multipart/form-data" class="checkout-form p-8 space-y-6">
                @csrf

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
                        <p class="font-semibold">Pesanan belum dapat dikirim:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center space-x-4">
                    <div class="w-14 h-14 bg-accent/10 text-accent rounded-xl flex items-center justify-center text-2xl">
                        <i class="fa-solid fa-car"></i>
                    </div>
                    <div class="space-y-1">
                    <p class="text-xs text-slate-400">KENDARAAN DIPILIH</p>
                    <h3 class="font-bold text-slate-900 text-base">{{ $kendaraan->nama_kendaraan }}</h3>
                    <p class="text-accent text-sm font-semibold">Tarif: Rp {{ number_format($kendaraan->tarif, 0, ',', '.') }} / hari</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Lengkap (Sesuai KTP)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="255" autocomplete="name" placeholder="Contoh: Dewi Lestari" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 transition">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Nomor WhatsApp / HP</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <input type="tel" name="nomor_telepon" value="{{ old('nomor_telepon') }}" required maxlength="24" autocomplete="tel" placeholder="Contoh: 081234567890" class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 transition">
                        </div>
                    </div>
                </div>

                <!-- Alamat Lengkap -->
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Alamat Domisili / Tempat Tinggal</label>
                    <div class="relative">
                        <span class="absolute top-3 left-4 text-slate-400">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <textarea name="alamat" rows="3" required maxlength="2000" autocomplete="street-address" placeholder="Masukkan alamat lengkap Anda di sini..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 transition">{{ old('alamat') }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Tanggal Mulai Sewa</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" min="{{ today()->toDateString() }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 transition">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Durasi (Hari)</label>
                        <input id="durasiSewa" type="number" name="durasi_hari" min="1" max="90" value="{{ old('durasi_hari', 1) }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 transition">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Upload Foto KTP (Jaminan Keamanan)</label>
                    <input type="file" name="ktp" accept="image/jpeg,image/png" required class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-accent file:text-white hover:file:bg-blue-700 transition">
                    <p class="text-xs text-slate-400">Foto KTP hanya dapat diakses admin dan maksimal 5 MB.</p>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                    <p class="text-sm font-semibold text-slate-700">Estimasi total: <span id="totalTarif" class="text-accent">Rp {{ number_format($kendaraan->tarif, 0, ',', '.') }}</span></p>
                    <button type="submit" class="px-8 py-3 bg-accent hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-lg shadow-blue-500/25">
                        Kirim Permintaan Sewa
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const durasiSewa = document.getElementById('durasiSewa');
        const totalTarif = document.getElementById('totalTarif');
        const tarifHarian = {{ Js::from($kendaraan->tarif) }};

        durasiSewa.addEventListener('input', function () {
            const total = tarifHarian * Math.max(1, Number(durasiSewa.value) || 1);
            totalTarif.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        });

        durasiSewa.dispatchEvent(new Event('input'));
    </script>
</body>
</html>