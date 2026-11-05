<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use App\Notifications\NewRentalBooking;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class TransaksiController extends Controller
{
    public function index(): View
    {
        return view('transaksi.index', [
            'transaksis' => Transaksi::query()
                ->with('pelanggan')
                ->latest()
                ->paginate(15),
            'totalMenunggu' => Transaksi::where('status', Transaksi::STATUS_MENUNGGU)->count(),
            'totalTransaksi' => Transaksi::query()->count(),
            'totalTransaksiSelesai' => Transaksi::query()
                ->where('status', Transaksi::STATUS_SELESAI)
                ->count(),
            'totalPendapatan' => Transaksi::query()
                ->where('status', Transaksi::STATUS_SELESAI)
                ->sum('total_tarif'),
        ]);
    }

    public function pelanggan(): View
    {
        return view('pelanggan.index', [
            'pelanggans' => Pelanggan::query()
                ->withCount('transaksis')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function downloadKtp(Pelanggan $pelanggan): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($pelanggan->ktp_path), 404);

        return Storage::disk('local')->download(
            $pelanggan->ktp_path,
            'ktp-pelanggan-'.$pelanggan->id.'.'.$this->ktpExtension($pelanggan->ktp_path),
        );
    }

    public function store(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nomor_telepon' => ['required', 'string', 'regex:/^\+?[0-9][0-9\s().-]{7,23}$/'],
            'alamat' => ['required', 'string', 'max:2000'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'durasi_hari' => ['required', 'integer', 'min:1', 'max:90'],
            'ktp' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $nomorTelepon = preg_replace('/[\s().-]/', '', $validated['nomor_telepon']);
        $durasiHari = (int) $validated['durasi_hari'];
        $ktpPath = null;
        $previousKtpPath = null;

        try {
            $transaksi = DB::transaction(function () use ($request, $kendaraan, $validated, $nomorTelepon, $durasiHari, &$ktpPath, &$previousKtpPath): Transaksi {
                $kendaraan = Kendaraan::query()->lockForUpdate()->findOrFail($kendaraan->id);
                $tanggalMulai = CarbonImmutable::parse($validated['tanggal_mulai']);
                $tanggalSelesai = $tanggalMulai->addDays($durasiHari);

                $hasConflict = Transaksi::query()
                    ->where('kendaraan_id', $kendaraan->id)
                    ->whereNotIn('status', [Transaksi::STATUS_SELESAI, Transaksi::STATUS_DIBATALKAN])
                    ->whereDate('tanggal_mulai', '<', $tanggalSelesai->toDateString())
                    ->whereDate('tanggal_selesai', '>', $tanggalMulai->toDateString())
                    ->exists();

                if ($hasConflict) {
                    throw ValidationException::withMessages([
                        'tanggal_mulai' => 'Kendaraan sudah dipesan pada periode tersebut. Silakan pilih tanggal lain.',
                    ]);
                }

                $pelanggan = Pelanggan::query()->firstOrNew(['nomor_telepon' => $nomorTelepon]);
                $previousKtpPath = $pelanggan->exists ? $pelanggan->ktp_path : null;
                $ktpPath = $request->file('ktp')->store('ktp', 'local');

                if (! is_string($ktpPath)) {
                    throw new \RuntimeException('Dokumen identitas pelanggan gagal disimpan.');
                }

                $pelanggan->fill([
                    'nama' => $validated['nama'],
                    'alamat' => $validated['alamat'],
                    'ktp_path' => $ktpPath,
                ])->save();

                $transaksi = Transaksi::create([
                    'pelanggan_id' => $pelanggan->id,
                    'kendaraan_id' => $kendaraan->id,
                    'nama_kendaraan' => $kendaraan->nama_kendaraan,
                    'plat_nomor' => $kendaraan->plat_nomor,
                    'tarif_harian' => $kendaraan->tarif,
                    'tanggal_mulai' => $tanggalMulai->toDateString(),
                    'tanggal_selesai' => $tanggalSelesai->toDateString(),
                    'durasi_hari' => $durasiHari,
                    'total_tarif' => $kendaraan->tarif * $durasiHari,
                    'status' => Transaksi::STATUS_MENUNGGU,
                ]);

                $this->syncKendaraanStatus($kendaraan);
                $transaksi->load('pelanggan');

                User::query()
                    ->where('role', 'admin')
                    ->get()
                    ->each(function (User $admin) use ($transaksi): void {
                        $alreadyNotified = $admin->notifications()
                            ->where('type', NewRentalBooking::class)
                            ->where('data->transaksi_id', $transaksi->id)
                            ->exists();

                        if (! $alreadyNotified) {
                            $admin->notify(new NewRentalBooking($transaksi));
                        }
                    });

                return $transaksi;
            });
        } catch (Throwable $exception) {
            if (is_string($ktpPath) && ! Storage::disk('local')->delete($ktpPath)) {
                Log::warning('Unable to remove an uncommitted customer identity document.');
            }

            throw $exception;
        }

        if ($previousKtpPath !== null && $previousKtpPath !== $ktpPath) {
            $this->deleteKtp($previousKtpPath, $transaksi->pelanggan_id);
        }

        return redirect()
            ->route('checkout.show', $kendaraan)
            ->with('success', 'Permintaan sewa berhasil dikirim dan menunggu konfirmasi admin.');
    }

    public function markNotificationRead(Request $request, string $notificationId): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin !== null && $admin->role === 'admin', 403);

        $notification = $admin->notifications()
            ->where('type', NewRentalBooking::class)
            ->whereKey($notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return redirect()->to(
            route('transaksi.index').'#transaksi-'.$notification->data['transaksi_id'],
        );
    }

    public function updateStatus(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Transaksi::STATUS_DIPESAN,
                Transaksi::STATUS_DISEWA,
                Transaksi::STATUS_SELESAI,
                Transaksi::STATUS_DIBATALKAN,
            ])],
        ]);

        DB::transaction(function () use ($transaksi, $validated): void {
            $transaksi = Transaksi::query()->lockForUpdate()->findOrFail($transaksi->id);
            $nextStatus = $validated['status'];
            $allowedTransitions = [
                Transaksi::STATUS_MENUNGGU => [Transaksi::STATUS_DIPESAN, Transaksi::STATUS_DIBATALKAN],
                Transaksi::STATUS_DIPESAN => [Transaksi::STATUS_DISEWA, Transaksi::STATUS_DIBATALKAN],
                Transaksi::STATUS_DISEWA => [Transaksi::STATUS_SELESAI, Transaksi::STATUS_DIBATALKAN],
            ];

            if (! in_array($nextStatus, $allowedTransitions[$transaksi->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Status transaksi tidak dapat diubah ke pilihan tersebut.',
                ]);
            }

            $transaksi->update(['status' => $nextStatus]);

            if ($transaksi->kendaraan_id !== null) {
                $kendaraan = Kendaraan::query()->find($transaksi->kendaraan_id);

                if ($kendaraan !== null) {
                    $this->syncKendaraanStatus($kendaraan);
                }
            }
        });

        return redirect()->route('transaksi.index')->with('success', 'Status transaksi berhasil diperbarui.');
    }

    private function syncKendaraanStatus(Kendaraan $kendaraan): void
    {
        $hariIni = today()->toDateString();
        $transaksisAktif = Transaksi::query()
            ->where('kendaraan_id', $kendaraan->id)
            ->whereIn('status', [
                Transaksi::STATUS_MENUNGGU,
                Transaksi::STATUS_DIPESAN,
                Transaksi::STATUS_DISEWA,
            ])
            ->get(['status', 'tanggal_mulai', 'tanggal_selesai']);

        $sedangDisewa = $transaksisAktif->contains(
            fn (Transaksi $transaksi): bool => $transaksi->status === Transaksi::STATUS_DISEWA
                && $transaksi->tanggal_mulai->toDateString() <= $hariIni
                && $transaksi->tanggal_selesai->toDateString() > $hariIni,
        );

        $kendaraan->update([
            'status' => $sedangDisewa
                ? 'Disewa'
                : ($transaksisAktif->isNotEmpty() ? 'Dipesan' : 'Tersedia'),
        ]);
    }

    private function deleteKtp(string $path, int $pelangganId): void
    {
        if (! Storage::disk('local')->delete($path)) {
            Log::warning('Unable to remove a replaced customer identity document.', [
                'pelanggan_id' => $pelangganId,
            ]);
        }
    }

    private function ktpExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
    }
}
