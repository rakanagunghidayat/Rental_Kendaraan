<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerawatanKendaraan extends Model
{
    public const STATUSES = [
        'Normal',
        'Perlu Servis',
        'Sedang Diperbaiki',
    ];

    protected $fillable = [
        'kendaraan_id',
        'nama_kendaraan',
        'plat_nomor',
        'servis_terakhir',
        'servis_berikutnya',
        'jenis_perawatan',
        'catatan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'servis_terakhir' => 'date',
            'servis_berikutnya' => 'date',
        ];
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class);
    }
}
