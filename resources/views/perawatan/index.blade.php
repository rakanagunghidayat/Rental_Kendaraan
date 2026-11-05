<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perawatan Kendaraan - {{ $websiteSettings->nama_website }}</title>
    <x-admin-theme-assets />
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
                <a href="{{ route('perawatan.index') }}" class="admin-page-nav__link rounded-lg bg-accent px-3 py-2 text-white" aria-current="page"><i class="fa-solid fa-screwdriver-wrench mr-2" aria-hidden="true"></i>Perawatan Kendaraan</a>
                <a href="{{ route('transaksi.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-file-invoice-dollar mr-2" aria-hidden="true"></i>Transaksi Sewa</a>
                <a href="{{ route('pelanggan.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-users mr-2" aria-hidden="true"></i>Pelanggan</a>
                <a href="{{ route('pengaturan-website.edit') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-gear mr-2" aria-hidden="true"></i>Pengaturan Website</a>
                <x-admin-theme-toggle />
                <form action="{{ url('/logout') }}" method="POST" class="admin-page-nav__logout-form">
                    @csrf
                    <button type="submit" class="admin-page-nav__logout"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i><span>Logout</span></button>
                </form>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-14">
        <div class="admin-page-title mb-8">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-accent mb-2">Perawatan Armada</p>
            <h1 class="text-3xl font-bold text-slate-900">Perawatan Kendaraan</h1>
            <p class="admin-page-title__description text-sm text-slate-500 mt-2">Catat servis, pantau jadwal, dan simpan riwayat setiap unit armada.</p>
            <div class="admin-page-title__accent" aria-hidden="true"></div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="maintenance-form-panel mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7">
            <div class="mb-6">
                <p class="text-xs font-semibold uppercase tracking-[.16em] text-blue-600">{{ $perawatan ? 'Perbarui catatan' : 'Catatan servis baru' }}</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $perawatan ? 'Edit perawatan kendaraan' : 'Tambah catatan perawatan' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Pilih kendaraan dari data armada. Status perawatan tidak mengubah status transaksi atau penyewaan.</p>
            </div>

            @if($kendaraans->isEmpty())
                <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                    Belum ada kendaraan untuk dicatat. Tambahkan kendaraan melalui tabel Daftar Kendaraan di Dashboard terlebih dahulu.
                </div>
            @else
                <form action="{{ $perawatan ? route('perawatan.update', $perawatan) : route('perawatan.store') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @csrf
                    @if($perawatan)
                        @method('PUT')
                    @endif
                    <label class="maintenance-field">
                        <span>Kendaraan</span>
                        <select name="kendaraan_id" required>
                            <option value="">Pilih kendaraan</option>
                            @foreach($kendaraans as $kendaraan)
                                <option value="{{ $kendaraan->id }}" @selected((string) old('kendaraan_id', $perawatan?->kendaraan_id) === (string) $kendaraan->id)>
                                    {{ $kendaraan->nama_kendaraan }} — {{ $kendaraan->plat_nomor }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="maintenance-field">
                        <span>Tanggal servis terakhir</span>
                        <input type="date" name="servis_terakhir" value="{{ old('servis_terakhir', $perawatan?->servis_terakhir?->format('Y-m-d')) }}" max="{{ today()->toDateString() }}" required>
                    </label>
                    <label class="maintenance-field">
                        <span>Jadwal servis berikutnya</span>
                        <input type="date" name="servis_berikutnya" value="{{ old('servis_berikutnya', $perawatan?->servis_berikutnya?->format('Y-m-d')) }}" required>
                    </label>
                    <label class="maintenance-field">
                        <span>Jenis perawatan</span>
                        <input type="text" name="jenis_perawatan" value="{{ old('jenis_perawatan', $perawatan?->jenis_perawatan) }}" maxlength="150" placeholder="Contoh: Ganti oli dan pemeriksaan rem" required>
                    </label>
                    <label class="maintenance-field">
                        <span>Status perawatan</span>
                        <select name="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $perawatan?->status ?? 'Normal') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="maintenance-field md:col-span-2 xl:col-span-3">
                        <span>Catatan perawatan <small>(opsional)</small></span>
                        <textarea name="catatan" rows="3" maxlength="2000" placeholder="Tambahkan rincian pekerjaan atau catatan teknisi">{{ old('catatan', $perawatan?->catatan) }}</textarea>
                    </label>
                    <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-3">
                        <button type="submit" class="maintenance-primary-button"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>{{ $perawatan ? 'Simpan Perubahan' : 'Tambah Catatan' }}</button>
                        @if($perawatan)
                            <a href="{{ route('perawatan.index') }}" class="maintenance-secondary-button">Batal</a>
                        @endif
                    </div>
                </form>
            @endif
        </section>

        <section class="transaction-panel overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-5 py-5 sm:px-6">
                <div>
                    <h2 class="font-bold text-slate-900">Daftar perawatan</h2>
                    <p class="mt-1 text-xs text-slate-500">Jadwal dalam 7 hari ditandai biru, jadwal terlewat ditandai merah.</p>
                </div>
                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">{{ $perawatans->total() }} catatan</span>
            </div>

            <form action="{{ route('perawatan.index') }}" method="GET" class="maintenance-filter flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-end sm:px-6">
                <label class="maintenance-field flex-1">
                    <span>Cari kendaraan atau nomor polisi</span>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Nama kendaraan / plat nomor">
                </label>
                <label class="maintenance-field sm:w-56">
                    <span>Filter status</span>
                    <select name="status">
                        <option value="">Semua status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected($statusFilter === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex gap-2">
                    <button type="submit" class="maintenance-primary-button"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>Filter</button>
                    <a href="{{ route('perawatan.index') }}" class="maintenance-secondary-button">Reset</a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[980px] border-collapse text-left">
                    <thead>
                        <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-4">Kendaraan</th>
                            <th class="px-5 py-4">Servis terakhir</th>
                            <th class="px-5 py-4">Servis berikutnya</th>
                            <th class="px-5 py-4">Perawatan</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        @forelse($perawatans as $catatan)
                            @php
                                $terlewat = $catatan->servis_berikutnya->lt(today());
                                $segera = ! $terlewat && $catatan->servis_berikutnya->lte(today()->addDays(7));
                            @endphp
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <span class="font-semibold text-slate-900">{{ $catatan->kendaraan?->nama_kendaraan ?? $catatan->nama_kendaraan }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $catatan->kendaraan?->plat_nomor ?? $catatan->plat_nomor }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">{{ $catatan->servis_terakhir->format('d/m/Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="block whitespace-nowrap">{{ $catatan->servis_berikutnya->format('d/m/Y') }}</span>
                                    <span @class([
                                        'maintenance-due-badge',
                                        'maintenance-due-badge--overdue' => $terlewat,
                                        'maintenance-due-badge--soon' => $segera,
                                        'maintenance-due-badge--normal' => ! $terlewat && ! $segera,
                                    ])>
                                        @if($terlewat)
                                            Jadwal servis terlewat
                                        @elseif($segera)
                                            {{ $catatan->servis_berikutnya->isToday() ? 'Servis hari ini' : 'Servis segera' }}
                                        @else
                                            Terjadwal
                                        @endif
                                    </span>
                                </td>
                                <td class="max-w-xs whitespace-normal px-5 py-4">
                                    <span class="font-medium text-slate-800">{{ $catatan->jenis_perawatan }}</span>
                                    @if($catatan->catatan)
                                        <span class="mt-1 block text-xs text-slate-500">{{ $catatan->catatan }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span @class([
                                        'maintenance-status-badge',
                                        'maintenance-status-badge--normal' => $catatan->status === 'Normal',
                                        'maintenance-status-badge--service' => $catatan->status === 'Perlu Servis',
                                        'maintenance-status-badge--repair' => $catatan->status === 'Sedang Diperbaiki',
                                    ])>{{ $catatan->status }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($catatan->kendaraan)
                                            <a href="{{ route('perawatan.history', $catatan->kendaraan) }}" class="maintenance-action-button" aria-label="Lihat riwayat {{ $catatan->kendaraan->nama_kendaraan }}" title="Riwayat"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></a>
                                        @endif
                                        <a href="{{ route('perawatan.edit', $catatan) }}" class="maintenance-action-button" aria-label="Edit perawatan {{ $catatan->nama_kendaraan }}" title="Edit"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                        <form action="{{ route('perawatan.destroy', $catatan) }}" method="POST" onsubmit="return confirm('Hapus catatan perawatan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="maintenance-action-button maintenance-action-button--delete" aria-label="Hapus perawatan {{ $catatan->nama_kendaraan }}" title="Hapus"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-slate-500">Belum ada catatan perawatan yang sesuai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($perawatans->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">{{ $perawatans->links() }}</div>
            @endif
        </section>
    </main>
</body>
</html>
