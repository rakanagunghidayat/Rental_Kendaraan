<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\PerawatanKendaraan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerawatanKendaraanController extends Controller
{
    public function index(Request $request): View
    {
        return $this->maintenanceIndex($request);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateMaintenance($request);
        $kendaraan = Kendaraan::query()->findOrFail($validated['kendaraan_id']);

        $kendaraan->perawatans()->create([
            ...$validated,
            'nama_kendaraan' => $kendaraan->nama_kendaraan,
            'plat_nomor' => $kendaraan->plat_nomor,
        ]);

        return redirect()->route('perawatan.index')
            ->with('success', 'Catatan perawatan kendaraan berhasil ditambahkan.');
    }

    public function edit(Request $request, PerawatanKendaraan $perawatan): View
    {
        return $this->maintenanceIndex($request, $perawatan);
    }

    public function update(Request $request, PerawatanKendaraan $perawatan): RedirectResponse
    {
        $validated = $this->validateMaintenance($request);
        $kendaraan = Kendaraan::query()->findOrFail($validated['kendaraan_id']);

        $perawatan->update([
            ...$validated,
            'nama_kendaraan' => $kendaraan->nama_kendaraan,
            'plat_nomor' => $kendaraan->plat_nomor,
        ]);

        return redirect()->route('perawatan.index')
            ->with('success', 'Catatan perawatan kendaraan berhasil diperbarui.');
    }

    public function destroy(PerawatanKendaraan $perawatan): RedirectResponse
    {
        $perawatan->delete();

        return redirect()->route('perawatan.index')
            ->with('success', 'Catatan perawatan kendaraan berhasil dihapus.');
    }

    public function history(Kendaraan $kendaraan): View
    {
        return view('perawatan.history', [
            'kendaraan' => $kendaraan,
            'riwayatPerawatan' => $kendaraan->perawatans()
                ->with('kendaraan')
                ->latest('servis_terakhir')
                ->latest('id')
                ->paginate(10),
        ]);
    }

    private function maintenanceIndex(Request $request, ?PerawatanKendaraan $perawatan = null): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', Rule::in(PerawatanKendaraan::STATUSES)],
        ]);
        $search = trim($filters['search'] ?? '');

        $perawatans = PerawatanKendaraan::query()
            ->with('kendaraan')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nama_kendaraan', 'like', "%{$search}%")
                        ->orWhere('plat_nomor', 'like', "%{$search}%")
                        ->orWhereHas('kendaraan', function (Builder $kendaraanQuery) use ($search): void {
                            $kendaraanQuery->where('nama_kendaraan', 'like', "%{$search}%")
                                ->orWhere('plat_nomor', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                isset($filters['status']) && $filters['status'] !== '',
                fn (Builder $query): Builder => $query->where('status', $filters['status']),
            )
            ->orderBy('servis_berikutnya')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('perawatan.index', [
            'kendaraans' => Kendaraan::query()->orderBy('nama_kendaraan')->get(),
            'perawatans' => $perawatans,
            'perawatan' => $perawatan,
            'search' => $search,
            'statusFilter' => $filters['status'] ?? '',
            'statuses' => PerawatanKendaraan::STATUSES,
        ]);
    }

    /**
     * @return array{kendaraan_id: int, servis_terakhir: string, servis_berikutnya: string, jenis_perawatan: string, catatan: string|null, status: string}
     */
    private function validateMaintenance(Request $request): array
    {
        return $request->validate([
            'kendaraan_id' => ['required', 'integer', 'exists:kendaraans,id'],
            'servis_terakhir' => ['required', 'date', 'before_or_equal:today'],
            'servis_berikutnya' => ['required', 'date', 'after_or_equal:servis_terakhir'],
            'jenis_perawatan' => ['required', 'string', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', Rule::in(PerawatanKendaraan::STATUSES)],
        ], [], [
            'kendaraan_id' => 'kendaraan',
            'servis_terakhir' => 'tanggal servis terakhir',
            'servis_berikutnya' => 'jadwal servis berikutnya',
            'jenis_perawatan' => 'jenis perawatan',
            'catatan' => 'catatan',
            'status' => 'status perawatan',
        ]);
    }
}
