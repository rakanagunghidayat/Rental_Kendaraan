<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Unit Kendaraan - {{ $websiteSettings->nama_website }}</title>
    <!-- Tailwind CSS & Font Poppins -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}">
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center p-6">
        <div class="edit-panel w-full max-w-xl bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
            
            <!-- Header Form -->
            <div class="flex items-center justify-between pb-6 border-b border-slate-100 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Edit Data Kendaraan</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi unit kendaraan rental.</p>
                </div>
                    <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition" aria-label="Kembali ke dashboard">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>

            @if($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
                        <p class="font-semibold">Periksa kembali data kendaraan:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
            @endif

            <!-- Form Edit -->
            <form action="{{ route('kendaraan.update', $kendaraan) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Nama Kendaraan</label>
                        <input type="text" name="nama_kendaraan" value="{{ old('nama_kendaraan', $kendaraan->nama_kendaraan) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Jenis Kendaraan</label>
                        <select name="jenis" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-600 bg-white">
                            <option value="Mobil" @selected(old('jenis', $kendaraan->jenis) === 'Mobil')>Mobil</option>
                            <option value="Motor" @selected(old('jenis', $kendaraan->jenis) === 'Motor')>Motor</option>
                    </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Plat Nomor</label>
                        <input type="text" name="plat_nomor" value="{{ old('plat_nomor', $kendaraan->plat_nomor) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Tarif / Hari (Rp)</label>
                        <input type="number" name="tarif" value="{{ old('tarif', $kendaraan->tarif) }}" min="0" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-600">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Status Unit</label>
                        <select name="status" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-600 bg-white">
                            <option value="Tersedia" @selected(old('status', $kendaraan->status) === 'Tersedia')>Tersedia</option>
                            <option value="Dipesan" @selected(old('status', $kendaraan->status) === 'Dipesan')>Dipesan</option>
                            <option value="Disewa" @selected(old('status', $kendaraan->status) === 'Disewa')>Disewa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Deskripsi Kendaraan</label>
                        <textarea name="deskripsi" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:border-blue-600">{{ old('deskripsi', $kendaraan->deskripsi) }}</textarea>
                    </div>

                    @if(!empty($kendaraan->foto))
                        <div class="flex flex-wrap gap-3">
                            @foreach($kendaraan->foto as $foto)
                                <img src="{{ asset('storage/' . $foto) }}" alt="Foto {{ $kendaraan->nama_kendaraan }}" class="h-20 w-20 rounded-xl object-cover">
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Ganti Foto Kendaraan (opsional)</label>
                        <input type="file" name="foto[]" multiple accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-white">
                        <p class="mt-1 text-xs text-slate-400">Pilih foto baru untuk menggantikan foto yang tersimpan. Maksimal 2 MB per foto.</p>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-lg shadow-blue-500/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</body>
</html>