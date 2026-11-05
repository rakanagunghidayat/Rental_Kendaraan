<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $websiteSettings->nama_website }} - {{ $kendaraan->nama_kendaraan }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}">
</head>
<body class="bg-slate-50 font-poppins">
    <div class="max-w-4xl mx-auto px-6 py-12">
        <a href="{{ url('/') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-accent mb-6">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Beranda
        </a>

        <div class="vehicle-detail bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm p-8 space-y-8">
            <!-- Informasi Utama -->
            <div class="vehicle-detail__header flex justify-between items-start">
                <div>
                    <span class="text-xs font-semibold text-accent uppercase tracking-wider">{{ $kendaraan->jenis }}</span>
                    <h1 class="text-3xl font-bold text-slate-900 mt-1">{{ $kendaraan->nama_kendaraan }}</h1>
                    <p class="text-sm text-slate-500 mt-1">Plat Nomor: {{ $kendaraan->plat_nomor }}</p>
                </div>
                <span class="px-4 py-1.5 rounded-full text-xs font-medium inline-flex items-center space-x-2 {{ $kendaraan->status == 'Tersedia' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                    <span class="w-2 h-2 rounded-full {{ $kendaraan->status == 'Tersedia' ? 'bg-emerald-600' : 'bg-rose-600' }}"></span>
                    <span>{{ $kendaraan->status }}</span>
                </span>
            </div>

            <!-- Galeri Multi-Foto (Menampilkan Semua Foto Sisi Kendaraan) -->
            <div>
                <h3 class="font-bold text-lg text-slate-900 mb-4">Galeri Foto Kendaraan</h3>
                @if(!empty($kendaraan->foto) && count($kendaraan->foto) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($kendaraan->foto as $foto)
                            <div class="h-48 rounded-2xl overflow-hidden border border-slate-200 bg-slate-100">
                                <img src="{{ asset('storage/' . $foto) }}" alt="Foto Kendaraan" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">Belum ada foto tambahan yang diunggah.</p>
                @endif
            </div>

            <!-- Deskripsi Kendaraan -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="font-bold text-lg text-slate-900 mb-2">Deskripsi & Fasilitas</h3>
                <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">{{ $kendaraan->deskripsi ?? 'Tidak ada deskripsi khusus untuk kendaraan ini.' }}</p>
            </div>

            <!-- Tarif & Tombol Aksi -->
            <div class="vehicle-detail__footer pt-6 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Tarif Sewa / Hari</p>
                    <p class="text-2xl font-bold text-accent">Rp {{ number_format($kendaraan->tarif, 0, ',', '.') }}</p>
                </div>
                <a href="{{ route('checkout.show', $kendaraan) }}" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-2xl transition shadow-lg shadow-blue-500/30 inline-block text-center" style="color: #ffffff !important;">
    Sewa Kendaraan Ini
</a>
            </div>
        </div>
    </div>
</body>
</html>