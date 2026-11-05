<?php

namespace App\Notifications;

use App\Models\Transaksi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRentalBooking extends Notification
{
    use Queueable;

    public function __construct(public Transaksi $transaksi) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{event: string, transaksi_id: int, kendaraan: string, plat_nomor: string, pelanggan: string}
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'event' => 'pemesanan_baru',
            'transaksi_id' => $this->transaksi->id,
            'kendaraan' => $this->transaksi->nama_kendaraan,
            'plat_nomor' => $this->transaksi->plat_nomor,
            'pelanggan' => $this->transaksi->pelanggan->nama,
        ];
    }
}
