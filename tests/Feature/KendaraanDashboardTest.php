<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KendaraanDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_uses_a_category_placeholder_when_a_vehicle_photo_is_missing(): void
    {
        Storage::fake('public');
        $kendaraan = $this->createKendaraan([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'foto' => ['kendaraan/editor-screenshot.jpg'],
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Honda Brio')
            ->assertSee('Rp 200.000')
            ->assertSee('Tersedia')
            ->assertSee('sr-vehicle-card__placeholder', false)
            ->assertSee('fa-car-side', false)
            ->assertSee(route('kendaraan.show', $kendaraan))
            ->assertSee(route('checkout.show', $kendaraan))
            ->assertDontSee(asset('storage/kendaraan/editor-screenshot.jpg'));
    }

    public function test_landing_page_uses_a_motor_placeholder_when_a_motor_photo_is_missing(): void
    {
        Storage::fake('public');
        $this->createKendaraan([
            'nama_kendaraan' => 'Honda Vario',
            'jenis' => 'Motor',
            'foto' => ['kendaraan/unrelated-screenshot.jpg'],
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Honda Vario')
            ->assertSee('sr-vehicle-card__placeholder', false)
            ->assertSee('fa-motorcycle', false)
            ->assertDontSee(asset('storage/kendaraan/unrelated-screenshot.jpg'));
    }

    public function test_landing_page_skips_missing_photo_files_for_a_vehicle(): void
    {
        Storage::fake('public');
        $photoPath = 'kendaraan/honda-brio.jpg';
        Storage::disk('public')->put($photoPath, 'photo');
        $this->createKendaraan([
            'nama_kendaraan' => 'Honda Brio',
            'foto' => ['kendaraan/missing-photo.jpg', $photoPath],
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSeeInOrder([Storage::disk('public')->url($photoPath), 'Honda Brio'])
            ->assertDontSee(asset('storage/kendaraan/missing-photo.jpg'));
    }

    public function test_admin_uploaded_photos_are_displayed_for_their_vehicles_after_reload(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());

        $this->post(route('kendaraan.store'), [
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'foto' => [UploadedFile::fake()->image('brio.jpg')],
        ])->assertRedirect(route('dashboard'));

        $this->post(route('kendaraan.store'), [
            'nama_kendaraan' => 'Honda Vario',
            'jenis' => 'Motor',
            'plat_nomor' => 'B 5678 EF',
            'tarif' => 85000,
            'foto' => [UploadedFile::fake()->image('vario.jpg')],
        ])->assertRedirect(route('dashboard'));

        $kendaraanMobil = Kendaraan::query()->where('nama_kendaraan', 'Honda Brio')->firstOrFail();
        $kendaraanMotor = Kendaraan::query()->where('nama_kendaraan', 'Honda Vario')->firstOrFail();
        $urlFotoMobil = Storage::disk('public')->url($kendaraanMobil->foto[0]);
        $urlFotoMotor = Storage::disk('public')->url($kendaraanMotor->foto[0]);

        Storage::disk('public')->assertExists($kendaraanMobil->foto[0]);
        Storage::disk('public')->assertExists($kendaraanMotor->foto[0]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSeeInOrder([$urlFotoMobil, 'Honda Brio', $urlFotoMotor, 'Honda Vario']);

        $this->get('/')
            ->assertOk()
            ->assertSee($urlFotoMobil)
            ->assertSee($urlFotoMotor);
    }

    public function test_replacing_a_vehicle_photo_keeps_unchanged_vehicle_photos_in_the_fleet(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $fotoLama = 'kendaraan/brio-lama.jpg';
        $fotoMotor = 'kendaraan/vario.jpg';
        Storage::disk('public')->put($fotoLama, 'old car photo');
        Storage::disk('public')->put($fotoMotor, 'motor photo');
        $kendaraanMobil = $this->createKendaraan([
            'nama_kendaraan' => 'Honda Brio',
            'foto' => [$fotoLama],
        ]);
        $kendaraanMotor = $this->createKendaraan([
            'nama_kendaraan' => 'Honda Vario',
            'jenis' => 'Motor',
            'foto' => [$fotoMotor],
        ]);

        $response = $this->put(route('kendaraan.update', $kendaraanMobil), [
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'foto' => [UploadedFile::fake()->image('brio-baru.jpg')],
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $kendaraanMobil->refresh();
        $kendaraanMotor->refresh();
        $urlFotoMobilBaru = Storage::disk('public')->url($kendaraanMobil->foto[0]);
        $urlFotoMotor = Storage::disk('public')->url($kendaraanMotor->foto[0]);
        Storage::disk('public')->assertExists($kendaraanMobil->foto[0]);
        Storage::disk('public')->assertExists($kendaraanMotor->foto[0]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([$urlFotoMobilBaru, 'Honda Brio', $urlFotoMotor, 'Honda Vario']);
    }

    public function test_landing_page_shows_an_empty_state_when_no_vehicles_are_registered(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('0 kendaraan terdaftar')
            ->assertSee('Armada belum tersedia')
            ->assertDontSee('Honda Brio');
    }

    public function test_landing_page_removes_the_service_section_and_hero_learn_more_targets_the_footer(): void
    {
        $this->createKendaraan();

        $response = $this->get('/');

        $response->assertOk()
            ->assertDontSee('sr-benefits', false)
            ->assertDontSee('LAYANAN SEWARIDE')
            ->assertDontSee('Dirancang agar perjalanan terasa lebih sederhana.')
            ->assertSee('<a id="heroLearnMore" class="sr-button sr-button--outline" href="#kontak">', false)
            ->assertSee('Pelajari Lebih Lanjut')
            ->assertSee('scrollIntoView({', false)
            ->assertSee("behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'", false)
            ->assertSee("block: 'end'", false)
            ->assertSee('document.querySelector(\'#kontak\').scrollIntoView({', false)
            ->assertSee('<a class="sr-button sr-button--primary" href="#armada">', false)
            ->assertSee('<a class="sr-button sr-button--nav" href="'.route('login').'">', false)
            ->assertSee('Dashboard Admin')
            ->assertSee('<section class="sr-how-it-works" id="layanan">', false)
            ->assertSee('Tiga langkah menuju perjalananmu.')
            ->assertSee('id="armada"', false)
            ->assertSee('id="kontak"', false)
            ->assertSee('Informasi kontak')
            ->assertSee('Cari nama kendaraan, merek, atau model');
    }

    public function test_landing_page_search_and_category_filters_render_database_vehicles(): void
    {
        $mobil = $this->createKendaraan([
            'nama_kendaraan' => 'Toyota Avanza Veloz',
            'jenis' => 'Mobil',
        ]);
        $motor = $this->createKendaraan([
            'nama_kendaraan' => 'Honda Vario 160',
            'jenis' => 'Motor',
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('css/sewaride.css?v=', false)
            ->assertSee('Cari nama kendaraan, merek, atau model…')
            ->assertSee('Hapus kata pencarian')
            ->assertSee('data-fleet-filter="Semua"', false)
            ->assertSee('data-fleet-filter="Mobil"', false)
            ->assertSee('data-fleet-filter="Motor"', false)
            ->assertSee('Kendaraan tidak ditemukan')
            ->assertSee('data-vehicle-name="Toyota Avanza Veloz" data-vehicle-type="Mobil"', false)
            ->assertSee('data-vehicle-name="Honda Vario 160" data-vehicle-type="Motor"', false)
            ->assertSee(route('checkout.show', $mobil))
            ->assertSee(route('checkout.show', $motor));
    }

    public function test_dashboard_shows_vehicle_rows_and_database_counts(): void
    {
        $this->actingAs(User::factory()->create());
        $this->createKendaraan(['nama_kendaraan' => 'Honda Brio', 'status' => 'Tersedia']);
        $this->createKendaraan(['nama_kendaraan' => 'Honda Vario', 'status' => 'Disewa']);

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Honda Brio')
            ->assertSee('Honda Vario')
            ->assertSee('2 Unit')
            ->assertSee('1 Unit')
            ->assertSee('class="admin-dashboard-page', false)
            ->assertSee('admin-brand__mark', false)
            ->assertSee('dashboard-stat--fleet', false)
            ->assertSee('css/sewaride.css?v=', false);
    }

    public function test_dashboard_displays_the_saved_vehicle_photo_and_logged_in_admin(): void
    {
        Storage::fake('public');
        $photoPath = 'kendaraan/dashboard-brio.jpg';
        Storage::disk('public')->put($photoPath, 'photo');
        $this->createKendaraan([
            'nama_kendaraan' => 'Honda Brio',
            'foto' => [$photoPath],
        ]);
        $this->actingAs(User::factory()->create([
            'name' => 'Raka Admin',
            'email' => 'raka@example.test',
        ]));

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(Storage::disk('public')->url($photoPath))
            ->assertSee('Foto Honda Brio')
            ->assertSee('Raka Admin')
            ->assertSee('raka@example.test');
    }

    public function test_dashboard_search_filters_vehicles_by_name_or_plate(): void
    {
        $this->actingAs(User::factory()->create());
        $this->createKendaraan(['nama_kendaraan' => 'Honda Brio', 'plat_nomor' => 'B 1234 CD']);
        $this->createKendaraan(['nama_kendaraan' => 'Toyota Avanza', 'plat_nomor' => 'D 5678 EF']);

        $response = $this->get(route('dashboard', ['search' => '5678']));

        $response->assertOk()
            ->assertSee('Toyota Avanza')
            ->assertDontSee('Honda Brio');
    }

    public function test_authenticated_admin_can_create_a_vehicle(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->post(route('kendaraan.store'), [
            'nama_kendaraan' => 'Honda Beat',
            'jenis' => 'Motor',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 85000,
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kendaraans', [
            'nama_kendaraan' => 'Honda Beat',
            'jenis' => 'Motor',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 85000,
            'status' => 'Tersedia',
        ]);
    }

    public function test_invalid_vehicle_data_is_rejected_without_creating_a_record(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->from(route('dashboard'))
            ->followingRedirects()
            ->post(route('kendaraan.store'), [
                'nama_kendaraan' => '',
                'jenis' => 'Truk',
                'plat_nomor' => '',
                'tarif' => -1,
            ]);

        $response->assertOk()
            ->assertSee('Data kendaraan belum bisa disimpan');

        $this->assertDatabaseCount('kendaraans', 0);
    }

    public function test_authenticated_admin_can_update_a_vehicle(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan();

        $response = $this->put(route('kendaraan.update', $kendaraan), [
            'nama_kendaraan' => 'Honda Brio RS',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 250000,
            'status' => 'Disewa',
            'deskripsi' => 'Mobil keluarga',
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'nama_kendaraan' => 'Honda Brio RS',
            'status' => 'Disewa',
            'deskripsi' => 'Mobil keluarga',
        ]);
    }

    public function test_authenticated_admin_can_open_the_vehicle_edit_page(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $photoPath = 'kendaraan/honda-brio.jpg';
        Storage::disk('public')->put($photoPath, 'photo');
        $kendaraan = $this->createKendaraan(['foto' => [$photoPath]]);

        $response = $this->get(route('kendaraan.edit', $kendaraan));

        $response->assertOk()
            ->assertSee('Edit Data Kendaraan')
            ->assertSee('Ganti Foto Kendaraan')
            ->assertSee(asset('storage/'.$photoPath));
    }

    public function test_authenticated_admin_can_delete_a_vehicle_and_its_photos(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $photoPath = 'kendaraan/honda-brio.jpg';
        Storage::disk('public')->put($photoPath, 'photo');
        $kendaraan = $this->createKendaraan(['foto' => [$photoPath]]);

        $response = $this->delete(route('kendaraan.destroy', $kendaraan));

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kendaraans', ['id' => $kendaraan->id]);
        Storage::disk('public')->assertMissing($photoPath);
    }

    public function test_guest_cannot_access_the_vehicle_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_the_vehicle_edit_page(): void
    {
        $kendaraan = $this->createKendaraan();

        $response = $this->get(route('kendaraan.edit', $kendaraan));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_a_vehicle(): void
    {
        $kendaraan = $this->createKendaraan();

        $response = $this->put(route('kendaraan.update', $kendaraan), []);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_a_vehicle(): void
    {
        $kendaraan = $this->createKendaraan();

        $response = $this->delete(route('kendaraan.destroy', $kendaraan));

        $response->assertRedirect(route('login'));
        $this->assertModelExists($kendaraan);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createKendaraan(array $attributes = []): Kendaraan
    {
        return Kendaraan::create(array_merge([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'deskripsi' => null,
            'foto' => [],
        ], $attributes));
    }
}
