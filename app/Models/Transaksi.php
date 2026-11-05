<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaksi extends Model
{
    public const STATUS_MENUNGGU = 'Menunggu Konfirmasi';

    public const STATUS_DIPESAN = 'Dipesan';

    public const STATUS_DISEWA = 'Disewa';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_DIBATALKAN = 'Dibatalkan';

    protected $fillable = [
        'pelanggan_id',
        'kendaraan_id',
        'nama_kendaraan',
        'jenis_kendaraan',
        'plat_nomor',
        'tarif_harian',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_hari',
        'total_tarif',
        'status',
        'invoice_number',
        'invoice_access_token_hash',
    ];

    protected $hidden = [
        'invoice_access_token_hash',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $transaksi): void {
            $transaksi->invoice_number ??= 'SWR-'.now()->format('Ymd').'-'.Str::upper((string) Str::uuid());
            $transaksi->invoice_access_token_hash ??= hash('sha256', Str::random(64));
            $transaksi->jenis_kendaraan ??= $transaksi->kendaraan_id === null
                ? 'Tidak diketahui'
                : (string) (Kendaraan::query()->whereKey($transaksi->kendaraan_id)->value('jenis') ?? 'Tidak diketahui');
        });
    }

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class);
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }
}
