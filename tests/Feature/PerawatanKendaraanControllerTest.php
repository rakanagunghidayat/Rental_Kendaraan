<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\PerawatanKendaraan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerawatanKendaraanControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_delete_and_review_vehicle_maintenance_history(): void
    {
        $this->actingAs($this->createAdmin());
        $kendaraan = $this->createKendaraan(['status' => 'Disewa']);

        $this->post(route('perawatan.store'), $this->validMaintenanceData($kendaraan))
            ->assertRedirect(route('perawatan.index'))
            ->assertSessionHas('success');

        $perawatan = PerawatanKendaraan::query()->firstOrFail();
        $this->assertDatabaseHas('perawatan_kendaraans', [
            'id' => $perawatan->id,
            'kendaraan_id' => $kendaraan->id,
            'nama_kendaraan' => 'Honda Brio',
            'plat_nomor' => 'B 1234 CD',
            'status' => 'Normal',
        ]);
        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Disewa',
        ]);

        $this->put(route('perawatan.update', $perawatan), $this->validMaintenanceData($kendaraan, [
            'status' => 'Sedang Diperbaiki',
            'jenis_perawatan' => 'Perbaikan rem',
        ]))->assertRedirect(route('perawatan.index'))
            ->assertSessionHas('success');

        $this->get(route('perawatan.history', $kendaraan))
            ->assertOk()
            ->assertSee('Perbaikan rem')
            ->assertSee('Sedang Diperbaiki');

        $this->delete(route('perawatan.destroy', $perawatan->refresh()))
            ->assertRedirect(route('perawatan.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('perawatan_kendaraans', ['id' => $perawatan->id]);
        $this->assertModelExists($kendaraan);
    }

    public function test_admin_can_search_by_vehicle_name_or_plate_filter_status_and_see_service_alerts(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 29)->setTime(12, 0));
        $this->actingAs($this->createAdmin());
        $honda = $this->createKendaraan([
            'nama_kendaraan' => 'Honda Brio',
            'plat_nomor' => 'B 1234 CD',
        ]);
        $toyota = $this->createKendaraan([
            'nama_kendaraan' => 'Toyota Avanza',
            'plat_nomor' => 'D 5678 EF',
        ]);

        $this->createPerawatan($honda, [
            'servis_berikutnya' => '2026-10-20',
            'status' => 'Perlu Servis',
        ]);
        $this->createPerawatan($toyota, [
            'servis_berikutnya' => '2026-11-02',
            'status' => 'Normal',
        ]);

        $this->get(route('perawatan.index', ['search' => '1234', 'status' => 'Perlu Servis']))
            ->assertOk()
            ->assertSee('Honda Brio')
            ->assertSee('Jadwal servis terlewat')
            ->assertViewHas('perawatans', fn ($perawatans): bool => $perawatans->total() === 1
                && $perawatans->first()->kendaraan_id === $honda->id);

        $this->get(route('perawatan.index', ['search' => '5678']))
            ->assertOk()
            ->assertSee('Toyota Avanza')
            ->assertSee('Servis segera')
            ->assertViewHas('perawatans', fn ($perawatans): bool => $perawatans->total() === 1
                && $perawatans->first()->kendaraan_id === $toyota->id);
    }

    public function test_maintenance_rejects_a_next_service_date_before_last_service(): void
    {
        $this->actingAs($this->createAdmin());
        $kendaraan = $this->createKendaraan();

        $this->from(route('perawatan.index'))
            ->post(route('perawatan.store'), $this->validMaintenanceData($kendaraan, [
                'servis_terakhir' => '2026-10-20',
                'servis_berikutnya' => '2026-10-19',
            ]))
            ->assertRedirect(route('perawatan.index'))
            ->assertSessionHasErrors('servis_berikutnya');

        $this->assertDatabaseCount('perawatan_kendaraans', 0);
    }

    public function test_maintenance_rejects_a_status_outside_the_supported_values(): void
    {
        $this->actingAs($this->createAdmin());
        $kendaraan = $this->createKendaraan();

        $this->from(route('perawatan.index'))
            ->post(route('perawatan.store'), $this->validMaintenanceData($kendaraan, [
                'status' => 'Status Tidak Dikenal',
            ]))
            ->assertRedirect(route('perawatan.index'))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('perawatan_kendaraans', 0);
    }

    public function test_deleting_a_vehicle_preserves_its_maintenance_history(): void
    {
        $this->actingAs($this->createAdmin());
        $kendaraan = $this->createKendaraan();
        $perawatan = $this->createPerawatan($kendaraan);

        $this->delete(route('kendaraan.destroy', $kendaraan))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('kendaraans', ['id' => $kendaraan->id]);
        $this->assertDatabaseHas('perawatan_kendaraans', [
            'id' => $perawatan->id,
            'kendaraan_id' => null,
            'nama_kendaraan' => 'Honda Brio',
            'plat_nomor' => 'B 1234 CD',
        ]);

        $this->get(route('perawatan.index', ['search' => '1234']))
            ->assertOk()
            ->assertSee('Honda Brio')
            ->assertSee('B 1234 CD');
    }

    public function test_guests_and_non_admin_users_cannot_access_maintenance_management(): void
    {
        $this->get(route('perawatan.index'))->assertRedirect(route('login'));
        $this->get(route('pengaturan-website.edit'))->assertRedirect(route('login'));

        $customer = User::factory()->create();
        $customer->forceFill(['role' => 'customer'])->save();

        $this->actingAs($customer)
            ->get(route('perawatan.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('pengaturan-website.edit'))
            ->assertForbidden();

        $kendaraan = $this->createKendaraan();

        $this->actingAs($customer)
            ->post(route('perawatan.store'), $this->validMaintenanceData($kendaraan))
            ->assertForbidden();

        $this->actingAs($customer)
            ->put(route('pengaturan-website.update'), ['nama_website' => 'Tidak berwenang'])
            ->assertForbidden();

        $this->assertDatabaseCount('perawatan_kendaraans', 0);
        $this->assertDatabaseCount('website_settings', 0);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createKendaraan(array $attributes = []): Kendaraan
    {
        return Kendaraan::query()->create(array_merge([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'deskripsi' => null,
            'foto' => [],
        ], $attributes));
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validMaintenanceData(Kendaraan $kendaraan, array $overrides = []): array
    {
        return array_merge([
            'kendaraan_id' => $kendaraan->id,
            'servis_terakhir' => '2026-10-20',
            'servis_berikutnya' => '2026-11-20',
            'jenis_perawatan' => 'Servis berkala',
            'catatan' => 'Pemeriksaan rutin',
            'status' => 'Normal',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPerawatan(Kendaraan $kendaraan, array $attributes = []): PerawatanKendaraan
    {
        return $kendaraan->perawatans()->create(array_merge([
            'nama_kendaraan' => $kendaraan->nama_kendaraan,
            'plat_nomor' => $kendaraan->plat_nomor,
            'servis_terakhir' => '2026-10-01',
            'servis_berikutnya' => '2026-11-01',
            'jenis_perawatan' => 'Servis berkala',
            'catatan' => null,
            'status' => 'Normal',
        ], $attributes));
    }
}
