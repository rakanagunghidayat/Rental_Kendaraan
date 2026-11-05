<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Website - {{ $websiteSettings->nama_website }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}?v={{ filemtime(public_path('css/sewaride.css')) }}">
</head>
<body class="font-poppins text-slate-800 antialiased min-h-screen">
    <header class="admin-page-header bg-white border-b border-slate-200">
        <div class="admin-page-header__inner max-w-7xl mx-auto px-6 lg:px-10 min-h-20 flex flex-wrap items-center justify-between gap-4 py-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="bg-accent text-white w-11 h-11 rounded-xl flex items-center justify-center shadow-lg"><img src="{{ $websiteSettings->logo_url }}" alt="" class="h-full w-full rounded-xl bg-white object-contain" aria-hidden="true"></span>
                <span><strong class="block text-slate-900">{{ $websiteSettings->nama_website }}</strong><small class="text-slate-500">Rental management</small></span>
            </a>
            <nav class="admin-page-nav flex flex-wrap items-center gap-2 text-sm font-semibold" aria-label="Navigasi admin">
                <a href="{{ route('dashboard') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-chart-pie mr-2" aria-hidden="true"></i>Dashboard</a>
                <a href="{{ route('perawatan.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-screwdriver-wrench mr-2" aria-hidden="true"></i>Perawatan Kendaraan</a>
                <a href="{{ route('transaksi.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-file-invoice-dollar mr-2" aria-hidden="true"></i>Transaksi Sewa</a>
                <a href="{{ route('pelanggan.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-users mr-2" aria-hidden="true"></i>Pelanggan</a>
                <a href="{{ route('pengaturan-website.edit') }}" class="admin-page-nav__link rounded-lg bg-accent px-3 py-2 text-white" aria-current="page"><i class="fa-solid fa-gear mr-2" aria-hidden="true"></i>Pengaturan Website</a>
                <form action="{{ url('/logout') }}" method="POST" class="admin-page-nav__logout-form">
                    @csrf
                    <button type="submit" class="admin-page-nav__logout"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Logout</span></button>
                </form>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-6 lg:px-10 py-10 lg:py-14">
        <div class="admin-page-title mb-8">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-accent mb-2">Konfigurasi</p>
            <h1 class="text-3xl font-bold text-slate-900">Pengaturan Website</h1>
            <p class="admin-page-title__description text-sm text-slate-500 mt-2">Informasi yang disimpan akan ditampilkan pada landing page {{ $websiteSettings->nama_website }}.</p>
            <div class="admin-page-title__accent" aria-hidden="true"></div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <form action="{{ route('pengaturan-website.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @csrf
                @method('PUT')

                <label class="maintenance-field">
                    <span>Nama website / usaha rental</span>
                    <input type="text" name="nama_website" value="{{ old('nama_website', $websiteSettings->nama_website) }}" maxlength="120" required>
                </label>

                <label class="maintenance-field">
                    <span>Email kontak</span>
                    <input type="email" name="email_kontak" value="{{ old('email_kontak', $websiteSettings->email_kontak) }}" maxlength="255" placeholder="contoh@email.com">
                </label>

                <div class="maintenance-field md:col-span-2">
                    <label for="logo" class="block"><span>Logo website</span></label>
                    <div class="mt-2 flex flex-wrap items-center gap-4">
                        @if($websiteSettings->logo_url)
                            <img src="{{ $websiteSettings->logo_url }}" alt="Logo website saat ini" class="website-settings-logo">
                        @else
                            <span class="website-settings-logo website-settings-logo--empty" aria-hidden="true"><i class="fa-solid fa-image"></i></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <input id="logo" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                            <small class="mt-1 block text-xs text-slate-500">Format JPG, PNG, atau WebP, maksimal 2 MB. Kosongkan jika tidak ingin mengganti logo.</small>
                        </div>
                    </div>
                </div>

                <div class="maintenance-field md:col-span-2">
                    <label for="foto_utama" class="block"><span>Foto Utama Landing Page</span></label>
                    <div class="mt-2 grid gap-3">
                        <img
                            id="foto-utama-preview"
                            src="{{ $websiteSettings->foto_utama_url }}"
                            data-current-src="{{ $websiteSettings->foto_utama_url }}"
                            alt="Foto utama landing page saat ini"
                            class="aspect-[1.13] w-full max-w-2xl rounded-2xl border border-slate-200 bg-slate-50 object-cover object-center shadow-sm"
                        >
                        <input id="foto_utama" type="file" name="foto_utama" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:font-semibold file:text-blue-700 hover:file:bg-blue-100">
                        <small id="foto-utama-status" class="text-xs text-slate-500">Foto yang sedang digunakan. Pilih foto baru untuk melihat preview sebelum disimpan.</small>
                        <small class="text-xs text-slate-500">Format JPG, JPEG, PNG, atau WebP, maksimal 2 MB. Kosongkan jika tidak ingin mengganti foto.</small>
                    </div>
                </div>

                <label class="maintenance-field md:col-span-2">
                    <span>Deskripsi singkat rental</span>
                    <textarea name="deskripsi" rows="3" maxlength="1000" placeholder="Jelaskan layanan rental">{{ old('deskripsi', $websiteSettings->deskripsi) }}</textarea>
                </label>

                <label class="maintenance-field md:col-span-2">
                    <span>Alamat rental</span>
                    <textarea name="alamat" rows="2" maxlength="1000" placeholder="Alamat yang ingin ditampilkan pada footer">{{ old('alamat', $websiteSettings->alamat) }}</textarea>
                </label>

                <label class="maintenance-field">
                    <span>Nomor WhatsApp</span>
                    <input type="tel" name="nomor_whatsapp" value="{{ old('nomor_whatsapp', $websiteSettings->nomor_whatsapp) }}" maxlength="40" placeholder="+6281234567890">
                    <small>Gunakan kode negara, misalnya +62.</small>
                </label>

                <label class="maintenance-field">
                    <span>Teks sambutan halaman utama</span>
                    <textarea name="teks_sambutan" rows="3" maxlength="500" placeholder="Teks utama di bagian hero">{{ old('teks_sambutan', $websiteSettings->teks_sambutan) }}</textarea>
                </label>

                <label class="maintenance-field md:col-span-2">
                    <span>Informasi footer</span>
                    <textarea name="informasi_footer" rows="3" maxlength="1000" placeholder="Informasi singkat yang tampil di footer">{{ old('informasi_footer', $websiteSettings->informasi_footer) }}</textarea>
                </label>

                <div class="flex flex-wrap gap-3 pt-2 md:col-span-2">
                    <button type="submit" class="maintenance-primary-button"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>Simpan Perubahan</button>
                    <a href="{{ route('dashboard') }}" class="maintenance-secondary-button">Kembali ke Dashboard</a>
                </div>
            </form>
        </section>
    </main>
    <script>
        const fotoUtamaInput = document.getElementById('foto_utama');
        const fotoUtamaPreview = document.getElementById('foto-utama-preview');
        const fotoUtamaStatus = document.getElementById('foto-utama-status');
        const fotoUtamaUrlSaatIni = fotoUtamaPreview.dataset.currentSrc;
        let fotoUtamaPreviewUrl;

        fotoUtamaInput.addEventListener('change', () => {
            const foto = fotoUtamaInput.files[0];

            if (!foto) {
                if (fotoUtamaPreviewUrl) {
                    URL.revokeObjectURL(fotoUtamaPreviewUrl);
                    fotoUtamaPreviewUrl = undefined;
                }

                fotoUtamaPreview.src = fotoUtamaUrlSaatIni;
                fotoUtamaPreview.alt = 'Foto utama landing page saat ini';
                fotoUtamaStatus.textContent = 'Foto yang sedang digunakan. Pilih foto baru untuk melihat preview sebelum disimpan.';
                return;
            }

            if (fotoUtamaPreviewUrl) {
                URL.revokeObjectURL(fotoUtamaPreviewUrl);
            }

            fotoUtamaPreviewUrl = URL.createObjectURL(foto);
            fotoUtamaPreview.src = fotoUtamaPreviewUrl;
            fotoUtamaPreview.alt = 'Preview foto utama landing page yang dipilih';
            fotoUtamaStatus.textContent = `Preview foto baru: ${foto.name}`;
        });
    </script>
</body>
</html>
