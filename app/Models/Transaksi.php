<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'plat_nomor',
        'tarif_harian',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi_hari',
        'total_tarif',
        'status',
    ];

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
