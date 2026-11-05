<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_dashboard_pages_include_the_persistent_theme_toggle(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin);
        $kendaraan = Kendaraan::create([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'foto' => [],
        ]);
        $dashboardPages = [
            route('dashboard'),
            route('kendaraan.edit', $kendaraan),
            route('transaksi.index'),
            route('pelanggan.index'),
            route('perawatan.index'),
            route('perawatan.history', $kendaraan),
            route('pengaturan-website.edit'),
        ];

        foreach ($dashboardPages as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('data-theme-toggle', false)
                ->assertSee('data-theme-icon="light"', false)
                ->assertSee('data-theme-icon="dark"', false)
                ->assertSee(asset('js/admin-theme.js'));
        }
    }
}
