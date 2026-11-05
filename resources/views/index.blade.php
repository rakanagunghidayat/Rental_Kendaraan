<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $websiteSettings->nama_website }} - Dashboard Pengelolaan Rental Motor & Mobil</title>
    <x-admin-theme-assets />
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}?v={{ filemtime(public_path('css/sewaride.css')) }}">
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
                        warning: '#F59E0B', // Amber 500
                    }
                }
            }
        }
    </script>
</head>
<body class="admin-dashboard-page font-poppins bg-slate-50 text-slate-800 antialiased">

    <div class="admin-shell flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside id="adminSidebar" class="admin-sidebar w-64 bg-primary text-slate-300 flex flex-col justify-between hidden md:flex shadow-xl">
            <div>
                <!-- Logo Brand -->
                <div class="admin-brand p-6 flex items-center space-x-3 border-b border-slate-800">
                    <div class="admin-brand__mark bg-accent text-white p-2.5 rounded-xl shadow-lg shadow-accent/30">
                        <img src="{{ $websiteSettings->logo_url }}" alt="" class="h-6 w-6 rounded object-contain" aria-hidden="true">
                    </div>
                    <div class="admin-brand__copy">
                        <h1 class="text-white font-bold text-lg tracking-wide">{{ $websiteSettings->nama_website }}</h1>
                        <p class="text-xs text-slate-400">RENTAL MANAGEMENT</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="admin-sidebar-nav p-4 space-y-1.5" aria-label="Navigasi dashboard">
                    <p class="admin-sidebar-nav__label">MENU UTAMA</p>
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-accent text-white font-medium shadow-md shadow-accent/20' : 'hover:bg-slate-800 hover:text-white' }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                        <i class="fa-solid fa-chart-pie w-5" aria-hidden="true"></i>
                        <span>Dashboard</span>
                    </a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('perawatan.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('perawatan.*') ? 'bg-accent text-white font-medium shadow-md shadow-accent/20' : 'hover:bg-slate-800 hover:text-white' }}" @if(request()->routeIs('perawatan.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-screwdriver-wrench w-5" aria-hidden="true"></i>
                            <span>Perawatan Kendaraan</span>
                        </a>
                    @endif
                    <a href="{{ route('transaksi.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('transaksi.*') ? 'bg-accent text-white font-medium shadow-md shadow-accent/20' : 'hover:bg-slate-800 hover:text-white' }}" @if(request()->routeIs('transaksi.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-file-invoice-dollar w-5" aria-hidden="true"></i>
                        <span>Transaksi Sewa</span>
                    </a>
                    <a href="{{ route('pelanggan.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('pelanggan.*') ? 'bg-accent text-white font-medium shadow-md shadow-accent/20' : 'hover:bg-slate-800 hover:text-white' }}" @if(request()->routeIs('pelanggan.*')) aria-current="page" @endif>
                        <i class="fa-solid fa-users w-5" aria-hidden="true"></i>
                        <span>Pelanggan</span>
                    </a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('pengaturan-website.edit') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('pengaturan-website.*') ? 'bg-accent text-white font-medium shadow-md shadow-accent/20' : 'hover:bg-slate-800 hover:text-white' }}" @if(request()->routeIs('pengaturan-website.*')) aria-current="page" @endif>
                            <i class="fa-solid fa-gear w-5" aria-hidden="true"></i>
                            <span>Pengaturan Website</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- User Profile Widget & Logout in Sidebar -->
            <div class="admin-sidebar-account p-4 border-t border-slate-800 space-y-3">
                <div class="admin-sidebar-account__profile flex items-center space-x-3 bg-slate-800/50 p-3 rounded-2xl">
                    <div class="admin-sidebar-account__avatar w-10 h-10 rounded-full bg-accent/20 text-accent flex items-center justify-center font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="admin-sidebar-account__role text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <!-- Tombol Logout -->
                <form action="{{ url('/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-logout w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500 text-rose-400 hover:text-white font-medium text-sm transition duration-200">
                        <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside> 

        <!-- Main Content Area -->
        <div class="admin-content flex-1 flex flex-col overflow-y-auto">
            <!-- Top Navbar -->
            <header class="admin-topbar bg-white border-b border-slate-200 h-20 flex items-center justify-between px-6 lg:px-10 sticky top-0 z-10">
                <div class="flex items-center space-x-4">
                    <button id="sidebarToggle" type="button" class="mobile-nav-toggle md:hidden text-slate-600 focus:outline-none" aria-label="Buka navigasi" aria-controls="adminSidebar" aria-expanded="false">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div class="admin-topbar__heading">
                        <p class="admin-topbar__eyebrow">{{ $websiteSettings->nama_website }} <span>/</span> ADMIN</p>
                        <h2 class="text-xl font-bold text-slate-900">Dashboard</h2>
                        <p class="text-xs text-slate-500">Kelola operasional rental motor dan mobil dengan mudah.</p>
                    </div>
                </div>

                <div class="admin-topbar__account flex items-center gap-3">
                    <x-admin-theme-toggle />
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                        <span class="admin-topbar__role block text-xs text-slate-500">Administrator</span>
                    </span>
                    @if(auth()->user()->role === 'admin')
                        <div id="notificationMenu" class="notification-menu">
                            <button
                                id="notificationToggle"
                                type="button"
                                class="notification-toggle w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition relative"
                                aria-label="Notifikasi{{ $jumlahNotifikasiBelumDibaca > 0 ? ', '.$jumlahNotifikasiBelumDibaca.' belum dibaca' : '' }}"
                                aria-controls="notificationDropdown"
                                aria-expanded="false"
                            >
                                <i class="fa-regular fa-bell" aria-hidden="true"></i>
                                @if($jumlahNotifikasiBelumDibaca > 0)
                                    <span class="notification-badge" aria-hidden="true">{{ $jumlahNotifikasiBelumDibaca > 99 ? '99+' : $jumlahNotifikasiBelumDibaca }}</span>
                                @endif
                            </button>
                            <section id="notificationDropdown" class="notification-dropdown hidden" aria-label="Notifikasi">
                                <header class="notification-dropdown__header">
                                    <div>
                                        <h2>Notifikasi</h2>
                                        <p>{{ $jumlahNotifikasiBelumDibaca }} belum dibaca</p>
                                    </div>
                                    <span class="notification-dropdown__header-icon" aria-hidden="true">
                                        <i class="fa-regular fa-bell"></i>
                                    </span>
                                </header>
                                <div class="notification-dropdown__list">
                                    @forelse($notifikasiTerbaru as $notifikasi)
                                        <form action="{{ route('notifications.read', $notifikasi->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" @class([
                                                'notification-item',
                                                'notification-item--unread' => $notifikasi->read_at === null,
                                            ])>
                                                <span class="notification-item__icon" aria-hidden="true">
                                                    <i class="fa-solid fa-calendar-plus"></i>
                                                </span>
                                                <span class="notification-item__body">
                                                    <span class="notification-item__title">Pemesanan baru</span>
                                                    <span class="notification-item__details">{{ $notifikasi->data['kendaraan'] ?? 'Kendaraan' }} · {{ $notifikasi->data['plat_nomor'] ?? '' }}</span>
                                                    <span class="notification-item__details">Pelanggan: {{ $notifikasi->data['pelanggan'] ?? 'Tidak diketahui' }}</span>
                                                    <time class="notification-item__time" datetime="{{ $notifikasi->created_at?->toIso8601String() }}">{{ $notifikasi->created_at?->diffForHumans() }}</time>
                                                </span>
                                                @if($notifikasi->read_at === null)
                                                    <span class="notification-item__unread-dot" aria-label="Belum dibaca"></span>
                                                @endif
                                            </button>
                                        </form>
                                    @empty
                                        <div class="notification-empty">
                                            <span class="notification-empty__icon" aria-hidden="true"><i class="fa-regular fa-bell-slash"></i></span>
                                            <p>Belum ada notifikasi</p>
                                            <span>Notifikasi pemesanan baru akan tampil di sini.</span>
                                        </div>
                                    @endforelse
                                </div>
                                <a class="notification-dropdown__footer" href="{{ route('transaksi.index') }}">Lihat transaksi <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            </section>
                        </div>
                    @endif
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="admin-dashboard p-6 lg:p-10 space-y-8">
                <section id="top" class="admin-dashboard__intro">
                    <div>
                        <p class="admin-dashboard__eyebrow"><span class="admin-dashboard__eyebrow-dot"></span> RINGKASAN OPERASIONAL</p>
                        <h1 class="text-2xl font-bold text-slate-900 mt-1">Kontrol armada, lebih mudah.</h1>
                        <p class="text-sm text-slate-500 mt-2">Pantau kendaraan dan aktivitas rental Anda dalam satu tampilan.</p>
                    </div>
                    <div class="admin-dashboard__context">
                        <span class="admin-dashboard__context-icon">
                            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
                        </span>
                        <span>
                            <span class="block text-xs text-slate-400">Pusat pengelolaan</span>
                            <span class="block text-sm font-semibold text-slate-700">Motor &amp; Mobil</span>
                        </span>
                    </div>
                </section>

                @if(session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Stats Overview Cards -->
                <div class="dashboard-stats grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5 gap-6">
                    <div class="dashboard-stat dashboard-stat--fleet bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Kendaraan</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $totalKendaraan }} Unit</h3>
                            </div>
                            <div class="dashboard-stat__icon w-12 h-12 bg-blue-50 text-accent rounded-2xl flex items-center justify-center text-xl">
                                <i class="fa-solid fa-car" aria-hidden="true"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat__caption">Seluruh unit kendaraan</p>
                    </div>

                    <div class="dashboard-stat dashboard-stat--rented bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sedang Disewa</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kendaraanDisewa }} Unit</h3>
                            </div>
                            <div class="dashboard-stat__icon w-12 h-12 bg-amber-50 text-warning rounded-2xl flex items-center justify-center text-xl">
                                <i class="fa-solid fa-key" aria-hidden="true"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat__caption">Kendaraan yang sedang disewa</p>
                    </div>

                    <div class="dashboard-stat dashboard-stat--pending bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kendaraan Dipesan</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kendaraanDipesan }} Unit</h3>
                            </div>
                            <div class="dashboard-stat__icon w-12 h-12 bg-amber-50 text-warning rounded-2xl flex items-center justify-center text-xl">
                                <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat__caption">Menunggu tanggal sewa atau konfirmasi</p>
                    </div>

                    <div class="dashboard-stat dashboard-stat--available bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kendaraan Tersedia</p>
                                <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ $kendaraanTersedia }} Unit</h3>
                            </div>
                            <div class="dashboard-stat__icon w-12 h-12 bg-emerald-50 text-success rounded-2xl flex items-center justify-center text-xl">
                                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat__caption">Siap disewakan</p>
                    </div>

                    <div class="dashboard-stat dashboard-stat--revenue bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Pendapatan</p>
                                <h3 class="dashboard-stat__amount text-2xl font-bold text-slate-900 mt-1">Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                            </div>
                            <div class="dashboard-stat__icon w-12 h-12 bg-blue-50 text-accent rounded-2xl flex items-center justify-center text-xl">
                                <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat__caption">Nilai transaksi selesai; pembayaran tidak tercatat terpisah</p>
                    </div>
                </div>

                <!-- Section Table / Main Data View -->
                <div id="kendaraan" class="table-panel dashboard-vehicle-panel bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="dashboard-vehicle-panel__header p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-bold text-slate-900">Daftar Kendaraan</h3>
                                <span class="vehicle-count">{{ $totalKendaraan }} unit</span>
                            </div>
                            <p class="text-xs text-slate-500">Kelola armada rental dan pantau status ketersediaannya.</p>
                        </div>
                        <div class="dashboard-vehicle-panel__tools flex flex-col sm:flex-row sm:items-center gap-3">
                            <form action="{{ route('dashboard') }}" method="GET" class="dashboard-search flex gap-2">
                                <label for="kendaraanSearch" class="sr-only">Cari kendaraan</label>
                                <input id="kendaraanSearch" type="search" name="search" value="{{ $search }}" placeholder="Cari nama, jenis, atau plat..." class="min-w-0 w-full sm:w-64 px-4 py-2 bg-slate-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50">
                                <button type="submit" class="dashboard-search__button w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition" aria-label="Cari kendaraan">
                                    <i class="fa-solid fa-search" aria-hidden="true"></i>
                                </button>
                            </form>
                            <button onclick="openModal()" class="dashboard-add-button px-4 py-2 bg-accent hover:bg-blue-700 text-white font-medium rounded-xl text-sm transition shadow-lg shadow-accent/30 flex items-center space-x-2">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>Tambah Unit</span>
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="vehicle-table w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-400 text-xs uppercase tracking-wider font-semibold border-b border-slate-200">
                                    <th class="py-4 px-6">Kendaraan</th>
                                    <th class="py-4 px-6">Jenis</th>
                                    <th class="py-4 px-6">Plat Nomor</th>
                                    <th class="py-4 px-6">Tarif / Hari</th>
                                    <th class="py-4 px-6">Status</th>
                                    <th class="py-4 px-6 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @forelse($kendaraans as $item)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            @if($fotoUtamaUrls->get($item->id))
                                                <img src="{{ $fotoUtamaUrls->get($item->id) }}" alt="Foto {{ $item->nama_kendaraan }}" class="vehicle-table__photo h-12 w-16 rounded-xl object-cover" loading="lazy">
                                            @else
                                                <div class="vehicle-table__icon w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 font-bold">
                                                    <i class="fa-solid {{ $item->jenis == 'Mobil' ? 'fa-car' : 'fa-motorcycle' }}" aria-hidden="true"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $item->nama_kendaraan }}</p>
                                                <p class="text-xs text-slate-400">{{ $item->jenis }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 font-medium">{{ $item->jenis }}</td>
                                    <td class="py-4 px-6 font-mono text-xs">{{ $item->plat_nomor }}</td>
                                    <td class="py-4 px-6 font-semibold text-slate-900">Rp {{ number_format($item->tarif, 0, ',', '.') }}</td>
                                    <td class="py-4 px-6">
                                    <span @class([
                                        'px-3 py-1 rounded-full text-xs font-medium inline-flex items-center space-x-1.5',
                                        'bg-emerald-50 text-emerald-700' => $item->status === 'Tersedia',
                                        'bg-amber-50 text-amber-700' => $item->status === 'Dipesan',
                                        'bg-blue-50 text-blue-700' => $item->status === 'Disewa',
                                        'bg-slate-100 text-slate-700' => ! in_array($item->status, ['Tersedia', 'Dipesan', 'Disewa'], true),
                                    ])>
                                        <span @class([
                                            'w-1.5 h-1.5 rounded-full',
                                            'bg-emerald-600' => $item->status === 'Tersedia',
                                            'bg-amber-600' => $item->status === 'Dipesan',
                                            'bg-blue-600' => $item->status === 'Disewa',
                                            'bg-slate-500' => ! in_array($item->status, ['Tersedia', 'Dipesan', 'Disewa'], true),
                                        ])></span>
                                        <span>{{ $item->status }}</span>
                                    </span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('kendaraan.edit', $item) }}" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-accent hover:text-white inline-flex items-center justify-center transition" aria-label="Edit {{ $item->nama_kendaraan }}">
                                                <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
                                            </a>
                                            <form action="{{ route('kendaraan.destroy', $item) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kendaraan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white inline-flex items-center justify-center transition" aria-label="Hapus {{ $item->nama_kendaraan }}">
                                                    <i class="fa-solid fa-trash text-xs" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="vehicle-table__empty">
                                        <span class="vehicle-table__empty-icon">
                                            <i class="fa-solid fa-car-side" aria-hidden="true"></i>
                                        </span>
                                        <span class="block font-semibold text-slate-700">{{ $search !== '' ? 'Tidak ada kendaraan yang cocok' : 'Belum ada kendaraan' }}</span>
                                        <span class="block mt-1 text-sm text-slate-500">{{ $search !== '' ? 'Coba kata kunci lain atau hapus pencarian.' : 'Tambahkan unit pertama untuk mulai mengelola armada rental.' }}</span>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Form Tambah Unit -->
    <div id="unitModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-8 space-y-6 shadow-2xl">
            <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                <h3 class="font-bold text-lg text-slate-900">Tambah Unit Kendaraan Baru</h3>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Form Laravel -->
            <form action="{{ route('kendaraan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700" role="alert">
                        <p class="font-semibold">Data kendaraan belum bisa disimpan:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <label class="text-xs font-semibold text-slate-600">Nama Kendaraan</label>
                    <input type="text" name="nama_kendaraan" value="{{ old('nama_kendaraan') }}" required placeholder="Contoh: Honda BeAT" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Jenis Kendaraan</label>
                    <select name="jenis" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50">
                        <option value="Mobil" @selected(old('jenis', 'Mobil') === 'Mobil')>Mobil</option>
                        <option value="Motor" @selected(old('jenis') === 'Motor')>Motor</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Plat Nomor</label>
                    <input type="text" name="plat_nomor" value="{{ old('plat_nomor') }}" required placeholder="Contoh: F 1234 AB" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Tarif / Hari (Rp)</label>
                    <input type="number" name="tarif" value="{{ old('tarif') }}" min="0" required placeholder="Contoh: 90000" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50">
                </div>

                <!-- Tambahan: Input Deskripsi Bebas -->
                <div>
                    <label class="text-xs font-semibold text-slate-600">Deskripsi Kendaraan</label>
                    <textarea name="deskripsi" rows="3" placeholder="Contoh: Kondisi mulus, mesin terawat, fasilitas helm 2 buah & jas hujan..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/50">{{ old('deskripsi') }}</textarea>
                </div>

                <!-- Tambahan: Input Multi Foto (Depan, Samping, Belakang) -->
                <div>
                    <label class="text-xs font-semibold text-slate-600">Foto Kendaraan (Bisa pilih banyak sekaligus: Depan, Samping, Belakang)</label>
                    <input type="file" name="foto[]" multiple accept="image/*" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-accent file:text-white hover:file:bg-blue-700">
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm">Batal</button>
                    <button type="submit" class="px-6 py-2.5 bg-accent hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-lg shadow-accent/30">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script Modal Interaksi -->
    <script>
        const sidebar = document.getElementById('adminSidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const notificationMenu = document.getElementById('notificationMenu');
        const notificationToggle = document.getElementById('notificationToggle');
        const notificationDropdown = document.getElementById('notificationDropdown');

        sidebarToggle.addEventListener('click', function () {
            const isOpen = sidebar.classList.toggle('is-open');
            sidebarToggle.setAttribute('aria-expanded', String(isOpen));
        });

        if (notificationMenu && notificationToggle && notificationDropdown) {
            notificationToggle.addEventListener('click', function () {
                const isOpen = !notificationDropdown.classList.toggle('hidden');
                notificationToggle.setAttribute('aria-expanded', String(isOpen));
            });

            document.addEventListener('click', function (event) {
                if (!notificationMenu.contains(event.target)) {
                    notificationDropdown.classList.add('hidden');
                    notificationToggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && !notificationDropdown.classList.contains('hidden')) {
                    notificationDropdown.classList.add('hidden');
                    notificationToggle.setAttribute('aria-expanded', 'false');
                    notificationToggle.focus();
                }
            });
        }

        sidebar.querySelectorAll('nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (link.hash === '#kendaraan') {
                    sidebar.querySelectorAll('nav a').forEach(function (navigationLink) {
                        navigationLink.classList.remove('bg-accent', 'text-white', 'font-medium', 'shadow-md', 'shadow-accent/20');
                        navigationLink.removeAttribute('aria-current');
                    });
                    link.classList.add('bg-accent', 'text-white', 'font-medium', 'shadow-md', 'shadow-accent/20');
                    link.setAttribute('aria-current', 'location');
                }

                if (window.matchMedia('(max-width: 767px)').matches) {
                    sidebar.classList.remove('is-open');
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }
            });
        });

        function openModal() {
            document.getElementById('unitModal').classList.remove('hidden');
            document.getElementById('unitModal').classList.add('flex');
        }
        function closeModal() {
            document.getElementById('unitModal').classList.add('hidden');
            document.getElementById('unitModal').classList.remove('flex');
        }

        @if($errors->any())
            openModal();
        @endif
    </script>
</body>
</html>