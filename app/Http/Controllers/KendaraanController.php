<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Notifications\NewRentalBooking;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class KendaraanController extends Controller
{
    // Menampilkan data otomatis ke Landing Page
    public function index()
    {
        $kendaraans = Kendaraan::all();
        $fotoUtamaUrls = $this->primaryPhotoUrls($kendaraans);

        return view('landing', compact('kendaraans', 'fotoUtamaUrls'));
    }

    public function checkout(Kendaraan $kendaraan): View
    {
        return view('checkout', compact('kendaraan'));
    }

    public function show(Kendaraan $kendaraan): View
    {
        return view('detail', compact('kendaraan'));
    }

    // Menampilkan data ke Dashboard Admin
    public function dashboard(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $kendaraanQuery = Kendaraan::query();

        if ($search !== '') {
            $kendaraanQuery->where(function (Builder $query) use ($search): void {
                $query->where('nama_kendaraan', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%")
                    ->orWhere('plat_nomor', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $kendaraans = $kendaraanQuery->orderByDesc('id')->get();
        $notifikasiTerbaru = collect();
        $jumlahNotifikasiBelumDibaca = 0;

        if ($request->user()->role === 'admin') {
            /** @var MorphMany $notifikasiAdmin */
            $notifikasiAdmin = $request->user()
                ->notifications()
                ->where('type', NewRentalBooking::class);

            $notifikasiTerbaru = (clone $notifikasiAdmin)
                ->latest()
                ->limit(8)
                ->get();
            $jumlahNotifikasiBelumDibaca = (clone $notifikasiAdmin)
                ->whereNull('read_at')
                ->count();
        }

        return view('index', [
            'kendaraans' => $kendaraans,
            'fotoUtamaUrls' => $this->primaryPhotoUrls($kendaraans),
            'search' => $search,
            'totalKendaraan' => Kendaraan::count(),
            'kendaraanTersedia' => Kendaraan::where('status', 'Tersedia')->count(),
            'kendaraanDipesan' => Kendaraan::where('status', 'Dipesan')->count(),
            'kendaraanDisewa' => Kendaraan::where('status', 'Disewa')->count(),
            'totalPendapatan' => Transaksi::query()
                ->where('status', Transaksi::STATUS_SELESAI)
                ->sum('total_tarif'),
            'notifikasiTerbaru' => $notifikasiTerbaru,
            'jumlahNotifikasiBelumDibaca' => $jumlahNotifikasiBelumDibaca,
        ]);
    }

    // Menyimpan data kendaraan baru dari Dashboard Admin
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kendaraan' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:Mobil,Motor'],
            'plat_nomor' => ['required', 'string', 'max:255'],
            'tarif' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'array'],
            'foto.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $fotoPaths = collect($request->file('foto', []))
            ->map(fn ($foto): string => $foto->store('kendaraan', 'public'))
            ->all();

        Kendaraan::create([
            'nama_kendaraan' => $validated['nama_kendaraan'],
            'jenis' => $validated['jenis'],
            'plat_nomor' => $validated['plat_nomor'],
            'tarif' => $validated['tarif'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'foto' => $fotoPaths,
        ]);

        return redirect()->route('dashboard')->with('success', 'Unit kendaraan berhasil ditambahkan.');
    }

    public function edit(Kendaraan $kendaraan): View
    {
        return view('edit-kendaraan', compact('kendaraan'));
    }

    public function update(Request $request, Kendaraan $kendaraan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kendaraan' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:Mobil,Motor'],
            'plat_nomor' => ['required', 'string', 'max:255'],
            'tarif' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:Tersedia,Dipesan,Disewa'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'array'],
            'foto.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $fotoLama = $kendaraan->foto ?? [];
        $fotoBaru = collect($request->file('foto', []))
            ->map(fn ($foto): string => $foto->store('kendaraan', 'public'))
            ->all();

        $kendaraan->update([
            'nama_kendaraan' => $validated['nama_kendaraan'],
            'jenis' => $validated['jenis'],
            'plat_nomor' => $validated['plat_nomor'],
            'tarif' => $validated['tarif'],
            'status' => $validated['status'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'foto' => $fotoBaru !== [] ? $fotoBaru : $fotoLama,
        ]);

        if ($fotoBaru !== []) {
            $this->deleteVehiclePhotos($fotoLama, $kendaraan->id);
        }

        return redirect()->route('dashboard')->with('success', 'Data kendaraan berhasil diperbarui.');
    }

    public function destroy(Kendaraan $kendaraan): RedirectResponse
    {
        $fotoPaths = $kendaraan->foto ?? [];
        $kendaraan->delete();

        $this->deleteVehiclePhotos($fotoPaths, $kendaraan->id);

        return redirect()->route('dashboard')->with('success', 'Kendaraan berhasil dihapus.');
    }

    /**
     * @param  array<int, string>  $fotoPaths
     */
    private function deleteVehiclePhotos(array $fotoPaths, int $kendaraanId): void
    {
        if ($fotoPaths !== [] && ! Storage::disk('public')->delete($fotoPaths)) {
            Log::warning('Unable to delete one or more vehicle photos.', [
                'kendaraan_id' => $kendaraanId,
            ]);
        }
    }

    /**
     * @param  Collection<int, Kendaraan>  $kendaraans
     * @return Collection<int, string|null>
     */
    private function primaryPhotoUrls(Collection $kendaraans): Collection
    {
        return $kendaraans->mapWithKeys(function (Kendaraan $kendaraan): array {
            $fotoPath = collect($kendaraan->foto ?? [])
                ->first(fn (mixed $path): bool => is_string($path) && Storage::disk('public')->exists($path));

            return [
                $kendaraan->id => is_string($fotoPath)
                    ? Storage::disk('public')->url($fotoPath)
                    : null,
            ];
        });
    }
}
