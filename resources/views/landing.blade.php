<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b1730">
    <meta name="description" content="{{ $websiteSettings->deskripsi }}">
    <title>{{ $websiteSettings->nama_website }} — Rental Motor &amp; Mobil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}?v={{ filemtime(public_path('css/sewaride.css')) }}">
    <link rel="preload" as="image" href="{{ $websiteSettings->foto_utama_url }}">
</head>
<body class="sewaride-landing">
    <header class="sr-header">
        <div class="sr-container sr-nav-shell">
            <a class="sr-brand" href="#beranda" aria-label="{{ $websiteSettings->nama_website }}, ke beranda">
                <span class="sr-brand__mark">
                    @if($websiteSettings->logo_url)
                        <img src="{{ $websiteSettings->logo_url }}" alt="" class="sr-brand__logo">
                    @else
                        <i class="fa-solid fa-road" aria-hidden="true"></i>
                    @endif
                </span>
                <span class="sr-brand__copy">
                    <strong>{{ $websiteSettings->nama_website }}</strong>
                    <small>Teman perjalananmu</small>
                </span>
            </a>

            <button class="sr-nav-toggle" type="button" aria-label="Buka navigasi" aria-controls="primary-navigation" aria-expanded="false">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <nav class="sr-nav" id="primary-navigation" aria-label="Navigasi utama">
                <a class="sr-nav__link" href="#beranda">Beranda</a>
                <a class="sr-nav__link" href="#layanan">Layanan</a>
                <a class="sr-nav__link" href="#armada">Armada</a>
                <a class="sr-nav__link" href="#kontak">Kontak</a>
                <a class="sr-button sr-button--nav" href="{{ route('login') }}">
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i>
                    Dashboard Admin
                </a>
            </nav>
        </div>
    </header>

    <main>
        <section class="sr-hero" id="beranda">
            <div class="sr-hero__glow sr-hero__glow--one" aria-hidden="true"></div>
            <div class="sr-hero__glow sr-hero__glow--two" aria-hidden="true"></div>
            <div class="sr-container sr-hero__layout">
                <div class="sr-hero__content">
                    <span class="sr-eyebrow sr-eyebrow--light">
                        <span class="sr-eyebrow__dot"></span>
                        Sewa motor &amp; mobil lebih mudah
                    </span>
                    <h1>{{ $websiteSettings->teks_sambutan }}</h1>
                    <p class="sr-hero__description">
                        {{ $websiteSettings->deskripsi }}
                    </p>
                    <div class="sr-hero__actions">
                        <a class="sr-button sr-button--primary" href="#armada">
                            Lihat Armada Kami
                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                        <a id="heroLearnMore" class="sr-button sr-button--outline" href="#kontak">
                            Pelajari Lebih Lanjut
                        </a>
                    </div>
                    <div class="sr-hero__trust">
                        <span class="sr-hero__trust-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                        <p><strong>Pemesanan transparan</strong><br>Permintaan sewa menunggu konfirmasi admin.</p>
                    </div>
                </div>

                <div class="sr-hero__visual">
                    <div class="sr-hero__image-frame">
                        <img src="{{ $websiteSettings->foto_utama_url }}" alt="Foto utama {{ $websiteSettings->nama_website }}" fetchpriority="high">
                        <div class="sr-hero__image-shade" aria-hidden="true"></div>
                        <span class="sr-hero__image-tag"><i class="fa-solid fa-car-side" aria-hidden="true"></i> Mobil &amp; motor</span>
                        <div class="sr-hero__image-caption">
                            <span class="sr-hero__caption-icon"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
                            <span>
                                <small>Temukan unit untuk perjalananmu</small>
                                <strong>{{ $kendaraans->count() }} kendaraan terdaftar</strong>
                            </span>
                            <i class="fa-solid fa-arrow-up-right-from-square sr-hero__caption-arrow" aria-hidden="true"></i>
                        </div>
                    </div>
                    <span class="sr-hero__visual-note">{{ $websiteSettings->foto_utama_path ? 'Foto utama armada' : 'Foto ilustrasi' }} · tersedia kategori mobil dan motor</span>
                    <div class="sr-hero__accent" aria-hidden="true"><i class="fa-solid fa-location-arrow"></i></div>
                </div>
            </div>
            <a href="#layanan" class="sr-hero__scroll" aria-label="Lihat layanan {{ $websiteSettings->nama_website }}">
                <span>Kenali layanan kami</span>
                <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
            </a>
        </section>

        <section class="sr-fleet" id="armada">
            <div class="sr-container">
                <div class="sr-fleet__heading">
                    <div class="sr-section-heading sr-section-heading--left">
                        <span class="sr-eyebrow">ARMADA KAMI</span>
                        <h2>Temukan unit untuk rencana berikutnya.</h2>
                        <p>Informasi kendaraan dan tarif ditampilkan dari data armada yang tersedia.</p>
                    </div>
                    <span class="sr-fleet__count"><i class="fa-solid fa-car-rear" aria-hidden="true"></i> {{ $kendaraans->count() }} unit terdaftar</span>
                </div>

                @if($kendaraans->isNotEmpty())
                    <div class="sr-fleet-tools">
                        <div class="sr-fleet-search">
                            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                            <input
                                id="fleetSearch"
                                type="search"
                                placeholder="Cari nama kendaraan, merek, atau model…"
                                aria-label="Cari nama kendaraan, merek, atau model"
                                autocomplete="off"
                            >
                            <button id="fleetSearchClear" type="button" aria-label="Hapus kata pencarian" hidden>
                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="sr-fleet-filters" role="group" aria-label="Filter kategori kendaraan">
                            <button type="button" class="is-active" data-fleet-filter="Semua" aria-pressed="true">Semua</button>
                            <button type="button" data-fleet-filter="Mobil" aria-pressed="false">Mobil</button>
                            <button type="button" data-fleet-filter="Motor" aria-pressed="false">Motor</button>
                        </div>
                    </div>
                    <p id="fleetResultCount" class="sr-fleet-result-count" aria-live="polite"></p>
                    <div class="sr-fleet-grid" id="fleetGrid">
                        @foreach($kendaraans as $item)
                            @php
                                $isMotor = $item->jenis === 'Motor';
                                $fotoUtamaUrl = $fotoUtamaUrls->get($item->id);
                            @endphp
                            <article class="sr-vehicle-card" data-vehicle-card data-vehicle-name="{{ $item->nama_kendaraan }}" data-vehicle-type="{{ $item->jenis }}">
                                <a class="sr-vehicle-card__image" href="{{ route('kendaraan.show', $item) }}" aria-label="Lihat detail {{ $item->nama_kendaraan }}">
                                    @if($fotoUtamaUrl)
                                        <img src="{{ $fotoUtamaUrl }}" alt="Foto {{ $item->nama_kendaraan }}" loading="lazy">
                                    @else
                                        <span class="sr-vehicle-card__placeholder" aria-hidden="true">
                                            <i class="fa-solid {{ $isMotor ? 'fa-motorcycle' : 'fa-car-side' }}"></i>
                                        </span>
                                    @endif
                                    <span class="sr-vehicle-card__type"><i class="fa-solid {{ $isMotor ? 'fa-motorcycle' : 'fa-car-side' }}" aria-hidden="true"></i> {{ $item->jenis }}</span>
                                </a>
                                <div class="sr-vehicle-card__body">
                                    <div class="sr-vehicle-card__title-row">
                                        <div>
                                            <span class="sr-vehicle-card__eyebrow">UNIT SEWARIDE</span>
                                            <h3>{{ $item->nama_kendaraan }}</h3>
                                        </div>
                                        <span @class([
                                            'sr-status',
                                            'sr-status--available' => $item->status === 'Tersedia',
                                            'sr-status--booked' => $item->status === 'Dipesan',
                                            'sr-status--unavailable' => ! in_array($item->status, ['Tersedia', 'Dipesan'], true),
                                        ])>
                                            <span class="sr-status__dot"></span>{{ $item->status }}
                                        </span>
                                    </div>
                                    <p class="sr-vehicle-card__plate"><i class="fa-regular fa-id-card" aria-hidden="true"></i> {{ $item->plat_nomor }}</p>
                                    <div class="sr-vehicle-card__footer">
                                        <div class="sr-vehicle-card__price">
                                            <span>Tarif per hari</span>
                                            <strong>Rp {{ number_format($item->tarif, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="sr-vehicle-card__actions">
                                            <a class="sr-button sr-button--small sr-button--secondary" href="{{ route('kendaraan.show', $item) }}">Detail</a>
                                            <a class="sr-button sr-button--small sr-button--primary" href="{{ route('checkout.show', $item) }}">Sewa</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <div id="fleetNoResults" class="sr-empty-state" hidden>
                        <span class="sr-empty-state__icon"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                        <h3>Kendaraan tidak ditemukan</h3>
                        <p>Coba kata pencarian lain atau ubah filter kategori.</p>
                    </div>
                @else
                    <div class="sr-empty-state">
                        <span class="sr-empty-state__icon"><i class="fa-solid fa-car-side" aria-hidden="true"></i></span>
                        <h3>Armada belum tersedia</h3>
                        <p>Belum ada kendaraan yang terdaftar. Silakan kembali lagi nanti.</p>
                    </div>
                @endif
            </div>
        </section>

        <section class="sr-how-it-works" id="layanan">
            <div class="sr-container sr-how-it-works__layout">
                <div class="sr-section-heading sr-section-heading--light sr-section-heading--left">
                    <span class="sr-eyebrow sr-eyebrow--light">CARA MENYEWA</span>
                    <h2>Tiga langkah menuju perjalananmu.</h2>
                    <p>Proses dimulai dari permintaan online dan dilanjutkan dengan konfirmasi admin.</p>
                    <a class="sr-text-link" href="#armada">Jelajahi armada <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <div class="sr-steps">
                    <article class="sr-step">
                        <span class="sr-step__number">01</span>
                        <div>
                            <h3>Pilih kendaraan</h3>
                            <p>Tinjau jenis, status, dan tarif harian unit yang tersedia.</p>
                        </div>
                    </article>
                    <article class="sr-step">
                        <span class="sr-step__number">02</span>
                        <div>
                            <h3>Kirim permintaan sewa</h3>
                            <p>Isi data pelanggan, jadwal sewa, dan unggah dokumen KTP.</p>
                        </div>
                    </article>
                    <article class="sr-step">
                        <span class="sr-step__number">03</span>
                        <div>
                            <h3>Tunggu konfirmasi admin</h3>
                            <p>Permintaan akan diproses sebelum sewa dikonfirmasi.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="sr-footer" id="kontak">
        <div class="sr-container">
            <div class="sr-footer__main">
                <a class="sr-brand sr-brand--footer" href="#beranda" aria-label="{{ $websiteSettings->nama_website }}, ke beranda">
                    <span class="sr-brand__mark">
                        @if($websiteSettings->logo_url)
                            <img src="{{ $websiteSettings->logo_url }}" alt="" class="sr-brand__logo">
                        @else
                            <i class="fa-solid fa-road" aria-hidden="true"></i>
                        @endif
                    </span>
                    <span class="sr-brand__copy">
                        <strong>{{ $websiteSettings->nama_website }}</strong>
                        <small>Teman perjalananmu</small>
                    </span>
                </a>
                <p>{{ $websiteSettings->informasi_footer }}</p>
                <div class="sr-footer__contact">
                    <h2>Informasi kontak</h2>
                    @if($websiteSettings->alamat)
                        <p><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $websiteSettings->alamat }}</p>
                    @endif
                    @if($websiteSettings->whatsapp_url)
                        <a href="{{ $websiteSettings->whatsapp_url }}" target="_blank" rel="noopener noreferrer">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>{{ $websiteSettings->nomor_whatsapp }}
                        </a>
                    @endif
                    @if($websiteSettings->email_kontak)
                        <a href="mailto:{{ $websiteSettings->email_kontak }}">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>{{ $websiteSettings->email_kontak }}
                        </a>
                    @endif
                </div>
                <a class="sr-footer__admin" href="{{ route('login') }}"><i class="fa-solid fa-lock" aria-hidden="true"></i> Masuk dashboard admin</a>
            </div>
            <div class="sr-footer__bottom">
                <span>&copy; {{ date('Y') }} {{ $websiteSettings->nama_website }}. Hak cipta dilindungi.</span>
                <a href="#beranda">Kembali ke atas <i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
            </div>
        </div>
    </footer>

    <script>
        const navToggle = document.querySelector('.sr-nav-toggle');
        const primaryNavigation = document.querySelector('#primary-navigation');

        navToggle.addEventListener('click', () => {
            const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';

            navToggle.setAttribute('aria-expanded', String(!isExpanded));
            navToggle.setAttribute('aria-label', isExpanded ? 'Buka navigasi' : 'Tutup navigasi');
            primaryNavigation.classList.toggle('is-open', !isExpanded);
        });

        primaryNavigation.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navToggle.setAttribute('aria-expanded', 'false');
                navToggle.setAttribute('aria-label', 'Buka navigasi');
                primaryNavigation.classList.remove('is-open');
            });
        });

        document.querySelector('#heroLearnMore').addEventListener('click', (event) => {
            event.preventDefault();
            document.querySelector('#kontak').scrollIntoView({
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                block: 'end',
            });
        });

        const fleetSearch = document.querySelector('#fleetSearch');
        const fleetSearchClear = document.querySelector('#fleetSearchClear');
        const fleetFilters = document.querySelectorAll('[data-fleet-filter]');
        const fleetCards = document.querySelectorAll('[data-vehicle-card]');
        const fleetNoResults = document.querySelector('#fleetNoResults');
        const fleetResultCount = document.querySelector('#fleetResultCount');
        let activeFleetFilter = 'Semua';

        if (fleetSearch && fleetSearchClear && fleetNoResults && fleetResultCount) {
            const filterFleet = () => {
                const searchTerm = fleetSearch.value.trim().toLocaleLowerCase('id');
                let visibleCount = 0;

                fleetCards.forEach((card) => {
                    const vehicleName = card.dataset.vehicleName.toLocaleLowerCase('id');
                    const vehicleType = card.dataset.vehicleType;
                    const matchesSearch = vehicleName.includes(searchTerm);
                    const matchesCategory = activeFleetFilter === 'Semua' || vehicleType === activeFleetFilter;
                    const isVisible = matchesSearch && matchesCategory;

                    card.hidden = !isVisible;
                    visibleCount += Number(isVisible);
                });

                fleetSearchClear.hidden = searchTerm.length === 0;
                fleetNoResults.hidden = visibleCount > 0;
                fleetResultCount.textContent = `Menampilkan ${visibleCount} dari ${fleetCards.length} kendaraan`;
            };

            fleetSearch.addEventListener('input', filterFleet);
            fleetSearchClear.addEventListener('click', () => {
                fleetSearch.value = '';
                filterFleet();
                fleetSearch.focus();
            });

            fleetFilters.forEach((filterButton) => {
                filterButton.addEventListener('click', () => {
                    activeFleetFilter = filterButton.dataset.fleetFilter;

                    fleetFilters.forEach((button) => {
                        const isActive = button === filterButton;

                        button.classList.toggle('is-active', isActive);
                        button.setAttribute('aria-pressed', String(isActive));
                    });

                    filterFleet();
                });
            });

            filterFleet();
        }
    </script>
</body>
</html>
