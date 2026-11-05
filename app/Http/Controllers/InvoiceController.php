<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function showPublic(string $token): Response
    {
        $transaksi = Transaksi::query()
            ->with('pelanggan')
            ->where('invoice_access_token_hash', hash('sha256', $token))
            ->firstOrFail();

        return $this->renderInvoice($transaksi);
    }

    public function showForAdmin(Transaksi $transaksi): Response
    {
        $transaksi->load('pelanggan');

        return $this->renderInvoice($transaksi);
    }

    private function renderInvoice(Transaksi $transaksi): Response
    {
        return response()
            ->view('invoice.show', [
                'transaksi' => $transaksi,
                'documentTitle' => $transaksi->status === Transaksi::STATUS_MENUNGGU
                    ? 'Bukti Permintaan Sewa'
                    : 'Invoice Pemesanan',
                'invoiceStatus' => match ($transaksi->status) {
                    Transaksi::STATUS_MENUNGGU => 'Menunggu Konfirmasi',
                    Transaksi::STATUS_DIPESAN => 'Dikonfirmasi',
                    Transaksi::STATUS_DISEWA => 'Sedang Disewa',
                    Transaksi::STATUS_SELESAI => 'Selesai',
                    Transaksi::STATUS_DIBATALKAN => 'Dibatalkan',
                    default => $transaksi->status,
                },
            ])
            ->header('Referrer-Policy', 'no-referrer')
            ->header('Cache-Control', 'private, no-store');
    }
}
