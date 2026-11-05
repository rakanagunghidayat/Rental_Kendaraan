<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>{{ $documentTitle }} {{ $transaksi->invoice_number }} - {{ $websiteSettings->nama_website }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/sewaride.css') }}?v={{ filemtime(public_path('css/sewaride.css')) }}">
</head>
<body class="invoice-page">
    <main class="invoice-page__container">
        <div class="invoice-actions" aria-label="Aksi dokumen">
            <button type="button" class="invoice-action-button" onclick="window.print()">
                <i class="fa-solid fa-print" aria-hidden="true"></i>
                {{ $documentTitle === 'Bukti Permintaan Sewa' ? 'Cetak Bukti Permintaan' : 'Cetak Invoice' }}
            </button>
            <button type="button" class="invoice-action-button invoice-action-button--primary" onclick="window.print()">
                <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i>
                Unduh PDF
            </button>
        </div>
        <p class="invoice-print-hint">Untuk menyimpan sebagai PDF, pilih "Save as PDF" pada dialog cetak browser.</p>

        <article class="invoice-document">
            <header class="invoice-document__header">
                <div class="invoice-brand">
                    <img src="{{ $websiteSettings->logo_url }}" alt="Logo {{ $websiteSettings->nama_website }}" referrerpolicy="no-referrer">
                    <div>
                        <p class="invoice-eyebrow">Dokumen pemesanan kendaraan</p>
                        <h1>{{ $websiteSettings->nama_website }}</h1>
                    </div>
                </div>
                <div class="invoice-number">
                    <span>{{ $documentTitle }}</span>
                    <strong>{{ $transaksi->invoice_number }}</strong>
                </div>
            </header>

            <section class="invoice-status" aria-label="Status transaksi">
                <span>Status transaksi</span>
                <strong>{{ $invoiceStatus }}</strong>
            </section>

            <section class="invoice-details-grid">
                <div>
                    <h2>Informasi pemesanan</h2>
                    <dl>
                        <div>
                            <dt>Tanggal pemesanan</dt>
                            <dd>{{ $transaksi->created_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Nama penyewa</dt>
                            <dd>{{ $transaksi->pelanggan->nama }}</dd>
                        </div>
                        <div>
                            <dt>Nomor WhatsApp</dt>
                            <dd>{{ $transaksi->pelanggan->nomor_telepon }}</dd>
                        </div>
                        <div>
                            <dt>Alamat</dt>
                            <dd class="invoice-address">{{ $transaksi->pelanggan->alamat }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <h2>Informasi kendaraan</h2>
                    <dl>
                        <div>
                            <dt>Nama kendaraan</dt>
                            <dd>{{ $transaksi->nama_kendaraan }}</dd>
                        </div>
                        <div>
                            <dt>Jenis kendaraan</dt>
                            <dd>{{ $transaksi->jenis_kendaraan }}</dd>
                        </div>
                        <div>
                            <dt>Periode sewa</dt>
                            <dd>{{ $transaksi->tanggal_mulai->format('d/m/Y') }} – {{ $transaksi->tanggal_selesai->copy()->subDay()->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt>Durasi</dt>
                            <dd>{{ $transaksi->durasi_hari }} hari</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="invoice-cost">
                <div>
                    <span>Harga sewa per hari</span>
                    <strong>Rp {{ number_format($transaksi->tarif_harian, 0, ',', '.') }}</strong>
                </div>
                <div class="invoice-cost__total">
                    <span>Total biaya pemesanan</span>
                    <strong>Rp {{ number_format($transaksi->total_tarif, 0, ',', '.') }}</strong>
                </div>
            </section>

            <footer class="invoice-disclaimer">
                <strong>Informasi pembayaran</strong>
                <p>Dokumen ini bukan bukti pembayaran. Pembayaran dilakukan di luar website sesuai kesepakatan antara penyewa dan admin.</p>
            </footer>
        </article>
    </main>

</body>
</html>
