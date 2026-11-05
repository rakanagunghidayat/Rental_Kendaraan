<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Sewa - {{ $websiteSettings->nama_website }}</title>
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
                <a href="{{ route('transaksi.index') }}" class="admin-page-nav__link rounded-lg bg-accent px-3 py-2 text-white" aria-current="page"><i class="fa-solid fa-file-invoice-dollar mr-2" aria-hidden="true"></i>Transaksi</a>
                <a href="{{ route('pelanggan.index') }}" class="admin-page-nav__link rounded-lg px-3 py-2 text-slate-600 hover:bg-slate-100"><i class="fa-solid fa-users mr-2" aria-hidden="true"></i>Pelanggan</a>
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
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-accent mb-2">Operasional</p>
            <h1 class="text-3xl font-bold text-slate-900">Transaksi sewa</h1>
            <p class="admin-page-title__description text-sm text-slate-500 mt-2">Kelola permintaan dan pantau jadwal penyewaan kendaraan.</p>
            <div class="admin-page-title__accent" aria-hidden="true"></div>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="transaction-summary-grid mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3" aria-label="Ringkasan transaksi">
            <article class="transaction-summary-card transaction-summary-card--transactions">
                <span class="transaction-summary-card__icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span>
                <div>
                    <p>Total Transaksi</p>
                    <h2>{{ number_format($totalTransaksi) }}</h2>
                    <span>Seluruh transaksi tercatat</span>
                </div>
            </article>
            <article class="transaction-summary-card transaction-summary-card--completed">
                <span class="transaction-summary-card__icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                <div>
                    <p>Transaksi Selesai</p>
                    <h2>{{ number_format($totalTransaksiSelesai) }}</h2>
                    <span>Status transaksi Selesai</span>
                </div>
            </article>
            <article class="transaction-summary-card transaction-summary-card--revenue">
                <span class="transaction-summary-card__icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span>
                <div>
                    <p>Total Pendapatan</p>
                    <h2>Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</h2>
                    <span>Nilai transaksi selesai; pembayaran tidak tercatat terpisah</span>
                </div>
            </article>
        </section>

        <section class="transaction-panel bg-white border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="font-bold text-slate-900">Daftar transaksi</h2>
                    <p class="text-xs text-slate-500 mt-1">Status menunggu konfirmasi: {{ $totalMenunggu }}</p>
                </div>
                <span class="px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">{{ $transaksis->total() }} transaksi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                            <th class="py-4 px-6">Pelanggan</th>
                            <th class="py-4 px-6">Kendaraan</th>
                            <th class="py-4 px-6">Periode sewa</th>
                            <th class="py-4 px-6">Total</th>
                            <th class="py-4 px-6">Status</th>
                            <th class="py-4 px-6">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        @forelse($transaksis as $transaksi)
                            <tr id="transaksi-{{ $transaksi->id }}" class="hover:bg-slate-50/80">
                                <td class="py-5 px-6">
                                    <span class="font-semibold text-slate-900">{{ $transaksi->pelanggan->nama }}</span>
                                    <span class="block text-xs text-slate-500 mt-1">{{ $transaksi->pelanggan->nomor_telepon }}</span>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="font-semibold text-slate-900">{{ $transaksi->nama_kendaraan }}</span>
                                    <span class="block text-xs text-slate-500 mt-1">{{ $transaksi->plat_nomor }}</span>
                                </td>
                                <td class="py-5 px-6">
                                    {{ $transaksi->tanggal_mulai->format('d/m/Y') }}
                                    <span class="text-slate-400">s.d.</span>
                                    {{ $transaksi->tanggal_selesai->copy()->subDay()->format('d/m/Y') }}
                                    <span class="block text-xs text-slate-500 mt-1">{{ $transaksi->durasi_hari }} hari</span>
                                </td>
                                <td class="py-5 px-6 font-semibold text-slate-900">Rp {{ number_format($transaksi->total_tarif, 0, ',', '.') }}</td>
                                <td class="py-5 px-6">
                                    <span @class([
                                        'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold',
                                        'bg-amber-50 text-amber-700' => $transaksi->status === \App\Models\Transaksi::STATUS_MENUNGGU,
                                        'bg-indigo-50 text-indigo-700' => $transaksi->status === \App\Models\Transaksi::STATUS_DIPESAN,
                                        'bg-blue-50 text-blue-700' => $transaksi->status === \App\Models\Transaksi::STATUS_DISEWA,
                                        'bg-emerald-50 text-emerald-700' => $transaksi->status === \App\Models\Transaksi::STATUS_SELESAI,
                                        'bg-rose-50 text-rose-700' => $transaksi->status === \App\Models\Transaksi::STATUS_DIBATALKAN,
                                    ])>{{ $transaksi->status }}</span>
                                </td>
                                <td class="py-5 px-6">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('transaksi.invoice', $transaksi) }}" class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            <i class="fa-solid fa-file-invoice mr-1" aria-hidden="true"></i>{{ $transaksi->status === \App\Models\Transaksi::STATUS_MENUNGGU ? 'Bukti Sewa' : 'Invoice' }}
                                        </a>
                                        <a href="{{ route('pelanggan.ktp', $transaksi->pelanggan) }}" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200">
                                            <i class="fa-solid fa-id-card mr-1" aria-hidden="true"></i> KTP
                                        </a>
                                        @if($transaksi->status === \App\Models\Transaksi::STATUS_MENUNGGU)
                                            <form action="{{ route('transaksi.status', $transaksi) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \App\Models\Transaksi::STATUS_DIPESAN }}">
                                                <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Konfirmasi</button>
                                            </form>
                                        @endif
                                        @if($transaksi->status === \App\Models\Transaksi::STATUS_DIPESAN)
                                            <form action="{{ route('transaksi.status', $transaksi) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \App\Models\Transaksi::STATUS_DISEWA }}">
                                                <button type="submit" class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700">Mulai Sewa</button>
                                            </form>
                                        @endif
                                        @if(in_array($transaksi->status, [\App\Models\Transaksi::STATUS_MENUNGGU, \App\Models\Transaksi::STATUS_DIPESAN, \App\Models\Transaksi::STATUS_DISEWA], true))
                                            @if($transaksi->status === \App\Models\Transaksi::STATUS_DISEWA)
                                                <form action="{{ route('transaksi.status', $transaksi) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ \App\Models\Transaksi::STATUS_SELESAI }}">
                                                    <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">Selesaikan</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('transaksi.status', $transaksi) }}" method="POST" onsubmit="return confirm('Batalkan transaksi ini?')">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ \App\Models\Transaksi::STATUS_DIBATALKAN }}">
                                                <button type="submit" class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">Batalkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-blue-50 text-xl text-accent">
                                        <i class="fa-solid fa-file-invoice" aria-hidden="true"></i>
                                    </span>
                                    <span class="block font-semibold text-slate-700">Belum ada transaksi sewa</span>
                                    <span class="mt-1 block text-sm text-slate-500">Permintaan sewa dari pelanggan akan tampil di sini.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transaksis->hasPages())
                <div class="border-t border-slate-200 px-6 py-4">{{ $transaksis->links() }}</div>
            @endif
        </section>
    </main>
</body>
</html>
