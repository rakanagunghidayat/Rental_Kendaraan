<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class WebsiteSettingsController extends Controller
{
    public function edit(): View
    {
        return view('pengaturan-website.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_website' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'foto_utama' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'nomor_whatsapp' => ['nullable', 'string', 'regex:/^\+?[0-9\s().-]{8,40}$/'],
            'email_kontak' => ['nullable', 'email', 'max:255'],
            'teks_sambutan' => ['nullable', 'string', 'max:500'],
            'informasi_footer' => ['nullable', 'string', 'max:1000'],
        ], [], [
            'nama_website' => 'nama website atau usaha rental',
            'logo' => 'logo website',
            'foto_utama' => 'foto utama landing page',
            'deskripsi' => 'deskripsi rental',
            'alamat' => 'alamat rental',
            'nomor_whatsapp' => 'nomor WhatsApp',
            'email_kontak' => 'email kontak',
            'teks_sambutan' => 'teks sambutan',
            'informasi_footer' => 'informasi footer',
        ]);

        $websiteSettings = WebsiteSetting::current();
        $logoLama = $websiteSettings->logo_path;
        $fotoUtamaLama = $websiteSettings->foto_utama_path;
        $logoBaru = $request->file('logo')?->store('website', 'public');
        $fotoUtamaBaru = $request->file('foto_utama')?->store('website/landing', 'public');

        if ($request->hasFile('logo') && $logoBaru === false) {
            throw new RuntimeException('Logo website gagal disimpan ke penyimpanan publik.');
        }

        if ($request->hasFile('foto_utama') && $fotoUtamaBaru === false) {
            throw new RuntimeException('Foto utama landing page gagal disimpan ke penyimpanan publik.');
        }

        unset($validated['logo']);
        unset($validated['foto_utama']);

        $websiteSettings->fill($validated);

        if (is_string($logoBaru)) {
            $websiteSettings->logo_path = $logoBaru;
        }

        if (is_string($fotoUtamaBaru)) {
            $websiteSettings->foto_utama_path = $fotoUtamaBaru;
        }

        $websiteSettings->save();

        if (is_string($logoBaru) && is_string($logoLama)) {
            $logoMasihDigunakan = WebsiteSetting::query()
                ->where(function (Builder $query) use ($logoLama): void {
                    $query->where('logo_path', $logoLama)
                        ->orWhere('foto_utama_path', $logoLama);
                })
                ->exists();

            if (! $logoMasihDigunakan && ! Storage::disk('public')->delete($logoLama)) {
                Log::warning('Unable to delete the previous website logo.', [
                    'website_setting_id' => $websiteSettings->id,
                ]);
            }
        }

        if (is_string($fotoUtamaBaru) && is_string($fotoUtamaLama)) {
            $fotoUtamaMasihDigunakan = WebsiteSetting::query()
                ->where(function (Builder $query) use ($fotoUtamaLama): void {
                    $query->where('foto_utama_path', $fotoUtamaLama)
                        ->orWhere('logo_path', $fotoUtamaLama);
                })
                ->exists();

            if (! $fotoUtamaMasihDigunakan && ! Storage::disk('public')->delete($fotoUtamaLama)) {
                Log::warning('Unable to delete the previous landing page hero image.', [
                    'website_setting_id' => $websiteSettings->id,
                ]);
            }
        }

        return redirect()->route('pengaturan-website.edit')
            ->with('success', 'Pengaturan website berhasil disimpan.');
    }
}
