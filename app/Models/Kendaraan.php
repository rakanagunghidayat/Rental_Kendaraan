<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kendaraan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kendaraan',
        'jenis',
        'plat_nomor',
        'tarif',
        'status',
        'deskripsi',
        'foto',
    ];

    protected $casts = [
        'foto' => 'array', // Agar multi-foto terbaca sebagai array
    ];

    public function perawatans(): HasMany
    {
        return $this->hasMany(PerawatanKendaraan::class);
    }
}
