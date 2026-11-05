<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WebsiteSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_settings_and_the_landing_page_uses_them(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());
        $fotoUtama = UploadedFile::fake()->image('foto-utama.jpg');

        $response = $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide Nusantara',
            'logo' => UploadedFile::fake()->image('logo.png'),
            'foto_utama' => $fotoUtama,
            'deskripsi' => 'Rental terpercaya untuk perjalanan harian.',
            'alamat' => 'Jl. Merdeka No. 10',
            'nomor_whatsapp' => '+62 812-3456-7890',
            'email_kontak' => 'halo@sewaride.test',
            'teks_sambutan' => 'Mulai petualangan dengan kendaraan pilihan.',
            'informasi_footer' => 'Melayani rental motor dan mobil sejak 2020.',
        ]);

        $response->assertRedirect(route('pengaturan-website.edit'))
            ->assertSessionHas('success');

        $websiteSettings = WebsiteSetting::query()->firstOrFail();
        $this->assertDatabaseHas('website_settings', [
            'id' => $websiteSettings->id,
            'nama_website' => 'SewaRide Nusantara',
            'deskripsi' => 'Rental terpercaya untuk perjalanan harian.',
            'alamat' => 'Jl. Merdeka No. 10',
            'nomor_whatsapp' => '+62 812-3456-7890',
            'email_kontak' => 'halo@sewaride.test',
            'teks_sambutan' => 'Mulai petualangan dengan kendaraan pilihan.',
            'informasi_footer' => 'Melayani rental motor dan mobil sejak 2020.',
        ]);
        Storage::disk('public')->assertExists($websiteSettings->logo_path);
        Storage::disk('public')->assertExists($websiteSettings->foto_utama_path);

        $this->get('/')
            ->assertOk()
            ->assertSee('SewaRide Nusantara')
            ->assertSee('Mulai petualangan dengan kendaraan pilihan.')
            ->assertSee('Rental terpercaya untuk perjalanan harian.')
            ->assertSee('Jl. Merdeka No. 10')
            ->assertSee('https://wa.me/6281234567890')
            ->assertSee('halo@sewaride.test')
            ->assertSee('Melayani rental motor dan mobil sejak 2020.')
            ->assertSee('href="'.$websiteSettings->foto_utama_url.'"', false)
            ->assertSee('src="'.$websiteSettings->foto_utama_url.'"', false)
            ->assertSee(Storage::disk('public')->url($websiteSettings->logo_path));

        $kendaraan = Kendaraan::query()->create([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'foto' => [],
        ]);

        $this->get(route('checkout.show', $kendaraan))
            ->assertOk()
            ->assertSee('SewaRide Nusantara - Formulir Pemesanan Kendaraan')
            ->assertSee(Storage::disk('public')->url($websiteSettings->logo_path));

        $this->get(route('kendaraan.show', $kendaraan))
            ->assertOk()
            ->assertSee('SewaRide Nusantara - Honda Brio');
    }

    public function test_admin_can_replace_the_landing_hero_photo_and_it_persists_after_refresh(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());
        $fotoLama = 'website/landing/foto-lama.jpg';
        Storage::disk('public')->put($fotoLama, 'old photo');
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide KP',
            'foto_utama_path' => $fotoLama,
        ]);
        $fotoUtama = UploadedFile::fake()->image('mobil-hero.webp');

        $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide KP',
            'foto_utama' => $fotoUtama,
        ])->assertRedirect(route('pengaturan-website.edit'))
            ->assertSessionHas('success');

        $websiteSettings = WebsiteSetting::query()->firstOrFail();
        $fotoPath = $websiteSettings->foto_utama_path;
        $fotoUrl = $websiteSettings->foto_utama_url;
        $this->assertNotSame($fotoLama, $fotoPath);
        Storage::disk('public')->assertMissing($fotoLama);
        Storage::disk('public')->assertExists($fotoPath);

        $this->get(route('pengaturan-website.edit'))
            ->assertOk()
            ->assertSee('Foto Utama Landing Page')
            ->assertSee('src="'.$fotoUrl.'"', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.$fotoUrl.'"', false)
            ->assertSee('src="'.$fotoUrl.'"', false);

        $this->get('/')
            ->assertOk()
            ->assertSee('src="'.$fotoUrl.'"', false);

        $this->assertDatabaseHas('website_settings', [
            'id' => $websiteSettings->id,
            'foto_utama_path' => $fotoPath,
        ]);
    }

    public function test_replacing_the_landing_photo_keeps_a_file_still_used_as_the_logo(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());
        $pathBersama = 'website/logo-dan-hero.png';
        Storage::disk('public')->put($pathBersama, 'shared image');
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide KP',
            'logo_path' => $pathBersama,
            'foto_utama_path' => $pathBersama,
        ]);

        $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide KP',
            'foto_utama' => UploadedFile::fake()->image('foto-hero-baru.jpg'),
        ])->assertRedirect(route('pengaturan-website.edit'));

        Storage::disk('public')->assertExists($pathBersama);
    }

    public function test_landing_page_uses_the_existing_default_hero_photo_without_an_upload(): void
    {
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide KP',
            'foto_utama_path' => null,
        ]);

        $defaultPhotoUrl = asset('images/sewaride-car.jpg').'?v='.filemtime(public_path('images/sewaride-car.jpg'));

        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.$defaultPhotoUrl.'"', false)
            ->assertSee('src="'.$defaultPhotoUrl.'"', false);
    }

    public function test_saved_identity_is_rendered_across_public_admin_and_login_views(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());

        $kendaraan = Kendaraan::query()->create([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'foto' => [],
        ]);

        $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide Nusantara',
            'logo' => UploadedFile::fake()->image('logo-nusantara.png'),
        ])->assertRedirect(route('pengaturan-website.edit'));

        $websiteSettings = WebsiteSetting::query()->firstOrFail();
        $logoUrl = $websiteSettings->logo_url;

        $this->get('/')
            ->assertOk()
            ->assertSee('SewaRide Nusantara')
            ->assertSee($logoUrl);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<h1 class="text-white font-bold text-lg tracking-wide">SewaRide Nusantara</h1>', false)
            ->assertSee('<p class="admin-topbar__eyebrow">SewaRide Nusantara <span>/</span> ADMIN</p>', false)
            ->assertSee('src="'.$logoUrl.'"', false);

        foreach ([
            route('transaksi.index'),
            route('pelanggan.index'),
            route('perawatan.index'),
            route('pengaturan-website.edit'),
            route('login'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('SewaRide Nusantara')
                ->assertSee($logoUrl);
        }

        $this->get(route('kendaraan.edit', $kendaraan))
            ->assertOk()
            ->assertSee('Edit Unit Kendaraan - SewaRide Nusantara');
    }

    public function test_default_logo_is_rendered_when_no_uploaded_logo_exists(): void
    {
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide Nusantara',
            'logo_path' => null,
        ]);

        $defaultLogoUrl = asset('images/sewaride-logo.svg').'?v='.filemtime(public_path('images/sewaride-logo.svg'));

        $this->get('/')
            ->assertOk()
            ->assertSee($defaultLogoUrl)
            ->assertSee('SewaRide Nusantara');
    }

    public function test_replacing_the_logo_keeps_the_new_upload_and_removes_the_previous_file(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());
        $logoLama = 'website/logo-lama.png';
        Storage::disk('public')->put($logoLama, 'old logo');
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide KP',
            'logo_path' => $logoLama,
        ]);
        $logoUrlLama = WebsiteSetting::query()->firstOrFail()->logo_url;

        $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide KP',
            'logo' => UploadedFile::fake()->image('logo-baru.webp'),
        ])->assertRedirect(route('pengaturan-website.edit'))
            ->assertSessionHas('success');

        $websiteSettings = WebsiteSetting::query()->firstOrFail();
        $this->assertNotSame($logoLama, $websiteSettings->logo_path);
        $this->assertNotSame($logoUrlLama, $websiteSettings->logo_url);
        $this->assertStringContainsString('?v=', $websiteSettings->logo_url);
        Storage::disk('public')->assertMissing($logoLama);
        Storage::disk('public')->assertExists($websiteSettings->logo_path);
    }

    public function test_replacing_a_logo_does_not_remove_a_file_referenced_by_another_setting(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());
        $logoLama = 'website/logo-bersama.png';
        Storage::disk('public')->put($logoLama, 'shared logo');
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide KP',
            'logo_path' => $logoLama,
        ]);
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide Arsip',
            'logo_path' => $logoLama,
        ]);

        $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide Baru',
            'logo' => UploadedFile::fake()->image('logo-baru.png'),
        ])->assertRedirect(route('pengaturan-website.edit'));

        Storage::disk('public')->assertExists($logoLama);
    }

    public function test_invalid_settings_and_logo_are_rejected_without_overwriting_saved_values(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());
        $logoLama = 'website/logo-tersimpan.png';
        Storage::disk('public')->put($logoLama, 'existing logo');
        WebsiteSetting::query()->create([
            'nama_website' => 'SewaRide KP',
            'logo_path' => $logoLama,
        ]);

        $this->from(route('pengaturan-website.edit'))
            ->put(route('pengaturan-website.update'), [
                'nama_website' => 'Nama berubah',
                'email_kontak' => 'bukan-email',
                'nomor_whatsapp' => 'nomor-palsu',
                'logo' => UploadedFile::fake()->create('logo.svg', 1, 'image/svg+xml'),
                'foto_utama' => UploadedFile::fake()->create('foto.svg', 1, 'image/svg+xml'),
            ])
            ->assertRedirect(route('pengaturan-website.edit'))
            ->assertSessionHasErrors(['email_kontak', 'nomor_whatsapp', 'logo', 'foto_utama']);

        $this->assertDatabaseHas('website_settings', [
            'nama_website' => 'SewaRide KP',
            'logo_path' => $logoLama,
            'foto_utama_path' => null,
        ]);
        Storage::disk('public')->assertExists($logoLama);
    }

    public function test_landing_hero_photo_uploads_larger_than_two_megabytes_are_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs($this->createAdmin());

        $this->from(route('pengaturan-website.edit'))
            ->put(route('pengaturan-website.update'), [
                'nama_website' => 'SewaRide KP',
                'foto_utama' => UploadedFile::fake()->image('foto-terlalu-besar.jpg')->size(2049),
            ])
            ->assertRedirect(route('pengaturan-website.edit'))
            ->assertSessionHasErrors('foto_utama');

        $this->assertDatabaseCount('website_settings', 0);
    }

    public function test_landing_page_escapes_saved_website_settings(): void
    {
        $this->actingAs($this->createAdmin());

        $this->put(route('pengaturan-website.update'), [
            'nama_website' => 'SewaRide',
            'deskripsi' => '<script>alert(1)</script>',
            'teks_sambutan' => 'Perjalanan aman',
        ])->assertRedirect(route('pengaturan-website.edit'));

        $this->get('/')
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_guest_and_non_admin_cannot_update_website_settings(): void
    {
        $this->get(route('pengaturan-website.edit'))->assertRedirect(route('login'));

        $customer = User::factory()->create();
        $customer->forceFill(['role' => 'customer'])->save();

        $this->actingAs($customer)
            ->put(route('pengaturan-website.update'), ['nama_website' => 'Tidak berwenang'])
            ->assertForbidden();

        $this->assertDatabaseCount('website_settings', 0);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }
}
