<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelanggan - {{ $websiteSettings->nama_website }}</title>
    <x-admin-theme-assets />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}">
</head>
<body class="font-poppins text-slate-800 antialiased min-h-screen">
    <header class="admin-page-header bg-white border-b border-slate-200">
        <div class="admin-page-header__inner max-w-7xl mx-auto px-6 lg:px-10 min-h-20 flex flex-wrap items-center justify-between gap-4 py-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <span class="bg-accent text-white w-11 h-11 rounded-xl flex items-center justify-center shadow-lg">
                    <img src="{{ $websiteSettings->logo_url }}" alt="" class="h-full w-full rounded-xl bg-white object-contain" aria-hidden="true">
                </span>
                <span>
                    <strong class="block text-slate-900">{{ $websiteSettings->nama_website }}</strong>
                    <small class="text-slate-500">Rental management</small>
                </span>
            </a>
            <nav class="admin-page-nav flex flex-wrap items-center gap-2 text-sm font-semibold" aria-label="Navigasi admin">
                <a href="{{ route('dashboard') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-chart-pie mr-2" aria-hidden="true"></i>Dashboard</a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('perawatan.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-screwdriver-wrench mr-2" aria-hidden="true"></i>Perawatan Kendaraan</a>
                @endif
                <a href="{{ route('transaksi.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-file-invoice-dollar mr-2" aria-hidden="true"></i>Transaksi</a>
                <a href="{{ route('pelanggan.index') }}" class="admin-page-nav__link rounded-lg bg-accent px-3 py-2 text-white" aria-current="page"><i class="fa-solid fa-users mr-2" aria-hidden="true"></i>Pelanggan</a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('pengaturan-website.edit') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-gear mr-2" aria-hidden="true"></i>Pengaturan Website</a>
                @endif
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
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-accent mb-2">Database</p>
            <h1 class="text-3xl font-bold text-slate-900">Pelanggan</h1>
            <p class="text-sm text-slate-500 mt-2">Data pelanggan tercatat otomatis saat mengirim permintaan sewa.</p>
            <div class="admin-page-title__accent" aria-hidden="true"></div>
        </div>

        <section class="transaction-panel bg-white border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-slate-900">Daftar pelanggan</h2>
                    <p class="text-xs text-slate-500 mt-1">Informasi identitas hanya dapat diakses oleh admin yang masuk.</p>
                </div>
                <span class="px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">{{ $pelanggans->total() }} pelanggan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="py-4 px-6">Nama</th>
                            <th class="py-4 px-6">Nomor WhatsApp</th>
                            <th class="py-4 px-6">Alamat</th>
                            <th class="py-4 px-6">Jumlah sewa</th>
                            <th class="py-4 px-6">Identitas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        @forelse($pelanggans as $pelanggan)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-5 px-6 font-semibold text-slate-900">{{ $pelanggan->nama }}</td>
                                <td class="py-5 px-6">{{ $pelanggan->nomor_telepon }}</td>
                                <td class="max-w-sm whitespace-normal py-5 px-6">{{ $pelanggan->alamat }}</td>
                                <td class="py-5 px-6">{{ $pelanggan->transaksis_count }} transaksi</td>
                                <td class="py-5 px-6">
                                    <a href="{{ route('pelanggan.ktp', $pelanggan) }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                                        Lihat KTP
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-xl text-accent">
                                        <i class="fa-solid fa-users" aria-hidden="true"></i>
                                    </span>
                                    <span class="block font-semibold text-slate-700">Belum ada data pelanggan</span>
                                    <span class="mt-1 block text-sm text-slate-500">Pelanggan akan tercatat setelah mengirim permintaan sewa.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pelanggans->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">{{ $pelanggans->links() }}</div>
            @endif
        </section>
    </main>
</body>
</html>
