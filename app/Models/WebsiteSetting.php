<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'nama_website',
        'logo_path',
        'foto_utama_path',
        'deskripsi',
        'alamat',
        'nomor_whatsapp',
        'email_kontak',
        'teks_sambutan',
        'informasi_footer',
    ];

    public static function current(): self
    {
        return self::query()->first() ?? new self([
            'nama_website' => 'SewaRide KP',
            'deskripsi' => 'Rental motor dan mobil dengan informasi armada yang jelas dan alur pemesanan yang terarah.',
            'teks_sambutan' => 'Perjalanan hebat dimulai dari kendaraan yang tepat.',
            'informasi_footer' => 'Rental motor dan mobil dengan informasi armada yang jelas dan alur pemesanan yang terarah.',
        ]);
    }

    public function getWhatsappUrlAttribute(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $this->nomor_whatsapp);

        return $number === '' ? null : 'https://wa.me/'.$number;
    }

    public function getLogoUrlAttribute(): string
    {
        if (is_string($this->logo_path) && Storage::disk('public')->exists($this->logo_path)) {
            $version = $this->updated_at?->format('Uu') ?? substr(hash('sha256', $this->logo_path), 0, 12);

            return Storage::disk('public')->url($this->logo_path).'?v='.$version;
        }

        $defaultLogo = 'images/sewaride-logo.svg';

        return asset($defaultLogo).'?v='.filemtime(public_path($defaultLogo));
    }

    public function getFotoUtamaUrlAttribute(): string
    {
        if (is_string($this->foto_utama_path) && Storage::disk('public')->exists($this->foto_utama_path)) {
            $version = $this->updated_at?->format('Uu') ?? substr(hash('sha256', $this->foto_utama_path), 0, 12);

            return Storage::disk('public')->url($this->foto_utama_path).'?v='.$version;
        }

        $defaultPhoto = 'images/sewaride-car.jpg';

        return asset($defaultPhoto).'?v='.filemtime(public_path($defaultPhoto));
    }
}
