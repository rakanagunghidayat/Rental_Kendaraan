<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use App\Notifications\NewRentalBooking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RentalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_customer_and_pending_rental_transaction(): void
    {
        Storage::fake('local');
        $kendaraan = $this->createKendaraan();

        $response = $this->post(route('checkout.store', $kendaraan), $this->validBookingData());

        $response->assertRedirect(route('checkout.show', $kendaraan))
            ->assertSessionHas('success');
        $response->assertSessionHas('invoice_url');

        $pelanggan = Pelanggan::query()->where('nomor_telepon', '081234567890')->firstOrFail();

        $this->assertDatabaseHas('pelanggans', [
            'nama' => 'Dewi Lestari',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Melati No. 5',
            'ktp_path' => $pelanggan->ktp_path,
        ]);
        $this->assertDatabaseHas('transaksis', [
            'kendaraan_id' => $kendaraan->id,
            'nama_kendaraan' => 'Honda Brio',
            'jenis_kendaraan' => 'Mobil',
            'tarif_harian' => 200000,
            'durasi_hari' => 3,
            'total_tarif' => 600000,
            'status' => Transaksi::STATUS_MENUNGGU,
        ]);
        $transaksi = Transaksi::query()->latest('id')->firstOrFail();
        $this->assertNotEmpty($transaksi->invoice_number);
        $this->assertNotEmpty($transaksi->invoice_access_token_hash);

        $checkoutResponse = $this->get(route('checkout.show', $kendaraan));
        $checkoutResponse->assertOk()
            ->assertSee('Lihat Bukti Permintaan Sewa');
        $this->assertSame(
            1,
            preg_match('/href="([^"]+)"[^>]*>Lihat Bukti Permintaan Sewa<\/a>/', (string) $checkoutResponse->getContent(), $invoiceLink),
        );
        $invoiceUrl = html_entity_decode($invoiceLink[1], ENT_QUOTES | ENT_HTML5);
        $this->assertMatchesRegularExpression('#/invoice/[A-Za-z0-9]{64}$#', $invoiceUrl);
        $this->assertNotSame(
            substr($invoiceUrl, strrpos($invoiceUrl, '/') + 1),
            $transaksi->invoice_access_token_hash,
        );
        $this->get($invoiceUrl)
            ->assertOk()
            ->assertSee('Bukti Permintaan Sewa')
            ->assertSee($transaksi->invoice_number);

        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Dipesan',
        ]);
        Storage::disk('local')->assertExists($pelanggan->ktp_path);
    }

    public function test_checkout_creates_one_database_notification_per_admin_for_the_saved_booking(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create();
        $anotherAdmin = User::factory()->create();
        $nonAdmin = User::factory()->create();
        $nonAdmin->forceFill(['role' => 'customer'])->save();
        $kendaraan = $this->createKendaraan();

        $response = $this->post(route('checkout.store', $kendaraan), $this->validBookingData());

        $response->assertRedirect(route('checkout.show', $kendaraan))
            ->assertSessionHas('success');

        $transaksi = Transaksi::query()->latest('id')->firstOrFail();
        $this->assertDatabaseCount('notifications', 2);

        foreach ([$admin, $anotherAdmin] as $adminUser) {
            $notification = $adminUser->notifications()->firstOrFail();

            $this->assertSame(NewRentalBooking::class, $notification->type);
            $this->assertSame([
                'event' => 'pemesanan_baru',
                'transaksi_id' => $transaksi->id,
                'kendaraan' => 'Honda Brio',
                'plat_nomor' => 'B 1234 CD',
                'pelanggan' => 'Dewi Lestari',
            ], $notification->data);
        }

        $this->assertSame(0, $nonAdmin->notifications()->count());
    }

    public function test_checkout_rejects_an_overlapping_rental_period(): void
    {
        Storage::fake('local');
        $kendaraan = $this->createKendaraan();
        $startDate = now()->addDays(2)->toDateString();
        $this->createTransaksi($kendaraan, [
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->from(route('checkout.show', $kendaraan))
            ->post(route('checkout.store', $kendaraan), $this->validBookingData([
                'tanggal_mulai' => now()->addDays(3)->toDateString(),
            ]));

        $response->assertRedirect(route('checkout.show', $kendaraan))
            ->assertSessionHasErrors('tanggal_mulai');

        $this->assertDatabaseCount('transaksis', 1);
        $this->assertDatabaseCount('pelanggans', 1);
        $this->assertSame([], Storage::disk('local')->allFiles('ktp'));
    }

    public function test_checkout_allows_adjacent_dates_and_reuses_the_customer_record(): void
    {
        Storage::fake('local');
        $kendaraan = $this->createKendaraan();

        $firstResponse = $this->post(route('checkout.store', $kendaraan), $this->validBookingData([
            'tanggal_mulai' => now()->addDays(2)->toDateString(),
        ]));
        $firstResponse->assertRedirect(route('checkout.show', $kendaraan))
            ->assertSessionHas('success');
        $firstTransaksi = Transaksi::query()->latest('id')->firstOrFail();
        $pelanggan = Pelanggan::query()->where('nomor_telepon', '081234567890')->firstOrFail();
        $previousKtpPath = $pelanggan->ktp_path;

        $secondResponse = $this->post(route('checkout.store', $kendaraan), $this->validBookingData([
            'tanggal_mulai' => $firstTransaksi->tanggal_selesai->toDateString(),
            'durasi_hari' => 1,
        ]));

        $secondResponse->assertRedirect(route('checkout.show', $kendaraan));
        $this->assertDatabaseCount('pelanggans', 1);
        $this->assertDatabaseCount('transaksis', 2);

        $pelanggan->refresh();
        Storage::disk('local')->assertMissing($previousKtpPath);
        Storage::disk('local')->assertExists($pelanggan->ktp_path);
    }

    public function test_customer_page_lists_saved_customer_data(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);
        $this->createPelanggan();

        $response = $this->get(route('pelanggan.index'));

        $response->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('081234567890')
            ->assertSee('Jl. Melati No. 5');
    }

    public function test_customer_page_escapes_customer_supplied_html(): void
    {
        $this->actingAs(User::factory()->create());
        $this->createPelanggan([
            'nama' => '<script>alert(1)</script>',
            'alamat' => '<img src=x onerror=alert(1)>',
        ]);

        $response = $this->get(route('pelanggan.index'));

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    public function test_dashboard_navigation_opens_vehicle_transaction_and_customer_pages(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin->refresh());

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee(route('perawatan.index'))
            ->assertSee(route('transaksi.index'))
            ->assertSee(route('pelanggan.index'))
            ->assertSee(route('pengaturan-website.edit'));
    }

    public function test_dashboard_notification_dropdown_shows_empty_and_saved_booking_notifications(): void
    {
        $admin = User::factory()->create();
        $admin->refresh();
        $this->assertSame('admin', $admin->role);
        $this->actingAs($admin);

        $emptyResponse = $this->get(route('dashboard'));

        $emptyResponse->assertOk()
            ->assertSee('Belum ada notifikasi')
            ->assertSee('notificationToggle')
            ->assertDontSee('notification-badge', false);

        $transaksi = $this->createTransaksi($this->createKendaraan());
        $admin->notify(new NewRentalBooking($transaksi));

        $response = $this->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('Pemesanan baru')
            ->assertSee('Honda Brio')
            ->assertSee('Dewi Lestari')
            ->assertSee('notification-badge')
            ->assertSee(route('notifications.read', $admin->notifications()->firstOrFail()->id));
    }

    public function test_admin_can_mark_own_booking_notification_as_read_and_open_transactions(): void
    {
        $admin = User::factory()->create();
        $admin->refresh();
        $transaksi = $this->createTransaksi($this->createKendaraan());
        $admin->notify(new NewRentalBooking($transaksi));
        $notification = $admin->notifications()->firstOrFail();
        $this->actingAs($admin);

        $response = $this->post(route('notifications.read', $notification->id));

        $response->assertRedirect(route('transaksi.index').'#transaksi-'.$transaksi->id);
        $this->assertNotNull($notification->fresh()->read_at);

        $dashboardResponse = $this->get(route('dashboard'));
        $dashboardResponse->assertOk()
            ->assertSee('Pemesanan baru')
            ->assertDontSee('notification-badge', false);
    }

    public function test_non_admin_and_other_admin_cannot_mark_an_unowned_booking_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $owner->refresh();
        $notificationOwner = User::factory()->create();
        $nonAdmin = User::factory()->create();
        $nonAdmin->forceFill(['role' => 'customer'])->save();
        $transaksi = $this->createTransaksi($this->createKendaraan());
        $notificationOwner->notify(new NewRentalBooking($transaksi));
        $notification = $notificationOwner->notifications()->firstOrFail();

        $this->actingAs($nonAdmin)
            ->post(route('notifications.read', $notification->id))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('notifications.read', $notification->id))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($nonAdmin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('id="notificationMenu"', false);
    }

    public function test_transaction_and_customer_pages_keep_admin_navigation_and_logout_available(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin->refresh());

        $transactionResponse = $this->get(route('transaksi.index'));
        $customerResponse = $this->get(route('pelanggan.index'));

        $transactionResponse->assertOk()
            ->assertSee(route('perawatan.index'))
            ->assertSee(route('pengaturan-website.edit'))
            ->assertSee(route('pelanggan.index'))
            ->assertSee('action="'.url('/logout').'"', false);
        $customerResponse->assertOk()
            ->assertSee(route('perawatan.index'))
            ->assertSee(route('pengaturan-website.edit'))
            ->assertSee(route('transaksi.index'))
            ->assertSee('action="'.url('/logout').'"', false);
    }

    public function test_checkout_form_posts_the_rental_details_to_the_server(): void
    {
        $kendaraan = $this->createKendaraan();

        $response = $this->get(route('checkout.show', $kendaraan));

        $response->assertOk()
            ->assertSee('name="nama"', false)
            ->assertSee('name="tanggal_mulai"', false)
            ->assertSee('name="durasi_hari"', false)
            ->assertSee('name="ktp"', false)
            ->assertSee(route('checkout.store', $kendaraan));
    }

    public function test_transaction_page_lists_saved_rental_data(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan();
        $this->createTransaksi($kendaraan);

        $response = $this->get(route('transaksi.index'));

        $response->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('Honda Brio')
            ->assertSee('Menunggu Konfirmasi');
    }

    public function test_dashboard_and_transaction_summaries_only_count_completed_rental_revenue(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan();
        $transaksiStatusDanTarif = [
            [Transaksi::STATUS_SELESAI, 1000000],
            [Transaksi::STATUS_SELESAI, 500000],
            [Transaksi::STATUS_MENUNGGU, 900000],
            [Transaksi::STATUS_DIBATALKAN, 1200000],
            [Transaksi::STATUS_DIPESAN, 250000],
            [Transaksi::STATUS_DISEWA, 400000],
        ];

        foreach ($transaksiStatusDanTarif as $index => [$status, $totalTarif]) {
            $pelanggan = $this->createPelanggan([
                'nomor_telepon' => '08123456789'.$index,
            ]);

            Transaksi::create([
                'pelanggan_id' => $pelanggan->id,
                'kendaraan_id' => $kendaraan->id,
                'nama_kendaraan' => $kendaraan->nama_kendaraan,
                'plat_nomor' => $kendaraan->plat_nomor,
                'tarif_harian' => $totalTarif,
                'tanggal_mulai' => now()->addDay()->toDateString(),
                'tanggal_selesai' => now()->addDays(2)->toDateString(),
                'durasi_hari' => 1,
                'total_tarif' => $totalTarif,
                'status' => $status,
            ]);
        }

        $dashboardResponse = $this->get(route('dashboard'));
        $transactionResponse = $this->get(route('transaksi.index'));

        $dashboardResponse->assertOk()
            ->assertSee('Total Pendapatan')
            ->assertSee('Rp1.500.000')
            ->assertSee('pembayaran tidak tercatat terpisah');
        $transactionResponse->assertOk()
            ->assertSee('Total Transaksi')
            ->assertSee('>6</h2>', false)
            ->assertSee('Transaksi Selesai')
            ->assertSee('>2</h2>', false)
            ->assertSee('Rp1.500.000')
            ->assertSee('pembayaran tidak tercatat terpisah')
            ->assertSee('admin-page-title__description');
    }

    public function test_admin_can_confirm_and_complete_a_rental(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan(['status' => 'Dipesan']);
        $transaksi = $this->createTransaksi($kendaraan, [
            'tanggal_mulai' => today()->addDays(2)->toDateString(),
            'tanggal_selesai' => today()->addDays(3)->toDateString(),
        ]);

        $confirmResponse = $this->patch(route('transaksi.status', $transaksi), [
            'status' => Transaksi::STATUS_DIPESAN,
        ]);

        $confirmResponse->assertRedirect(route('transaksi.index'));
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'status' => Transaksi::STATUS_DIPESAN,
        ]);
        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Dipesan',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Dipesan',
            'bg-amber-50 text-amber-700',
            'sr-status--booked',
        );

        $startResponse = $this->patch(route('transaksi.status', $transaksi), [
            'status' => Transaksi::STATUS_DISEWA,
        ]);

        $startResponse->assertRedirect(route('transaksi.index'));
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'status' => Transaksi::STATUS_DISEWA,
        ]);
        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Disewa',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Disewa',
            'bg-blue-50 text-blue-700',
            'sr-status--unavailable',
        );

        $completeResponse = $this->patch(route('transaksi.status', $transaksi), [
            'status' => Transaksi::STATUS_SELESAI,
        ]);

        $completeResponse->assertRedirect(route('transaksi.index'));
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'status' => Transaksi::STATUS_SELESAI,
        ]);
        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Tersedia',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Tersedia',
            'bg-emerald-50 text-emerald-700',
            'sr-status--available',
        );
    }

    public function test_vehicle_stays_unavailable_until_all_rentals_are_completed_or_cancelled(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan(['status' => 'Disewa']);
        $transaksiDisewa = $this->createTransaksi($kendaraan, [
            'status' => Transaksi::STATUS_DISEWA,
            'tanggal_mulai' => today()->addDays(5)->toDateString(),
            'tanggal_selesai' => today()->addDays(7)->toDateString(),
        ]);
        $pelangganKedua = $this->createPelanggan([
            'nomor_telepon' => '081234567891',
        ]);
        $transaksiDipesan = $this->createTransaksi($kendaraan, [
            'pelanggan_id' => $pelangganKedua->id,
            'status' => Transaksi::STATUS_DIPESAN,
            'tanggal_mulai' => today()->addDays(8)->toDateString(),
            'tanggal_selesai' => today()->addDays(10)->toDateString(),
        ]);

        $this->patch(route('transaksi.status', $transaksiDisewa), [
            'status' => Transaksi::STATUS_SELESAI,
        ])->assertRedirect(route('transaksi.index'));

        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Dipesan',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Dipesan',
            'bg-amber-50 text-amber-700',
            'sr-status--booked',
        );

        $this->patch(route('transaksi.status', $transaksiDipesan), [
            'status' => Transaksi::STATUS_DIBATALKAN,
        ])->assertRedirect(route('transaksi.index'));

        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Tersedia',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Tersedia',
            'bg-emerald-50 text-emerald-700',
            'sr-status--available',
        );
    }

    public function test_admin_can_cancel_a_pending_rental_and_release_the_vehicle(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan(['status' => 'Dipesan']);
        $transaksi = $this->createTransaksi($kendaraan);

        $response = $this->patch(route('transaksi.status', $transaksi), [
            'status' => Transaksi::STATUS_DIBATALKAN,
        ]);

        $response->assertRedirect(route('transaksi.index'));
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'status' => Transaksi::STATUS_DIBATALKAN,
        ]);
        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Tersedia',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Tersedia',
            'bg-emerald-50 text-emerald-700',
            'sr-status--available',
        );
    }

    public function test_cancelling_a_reservation_does_not_make_another_active_rental_available(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan(['status' => 'Disewa']);
        $transaksiDisewa = $this->createTransaksi($kendaraan, [
            'status' => Transaksi::STATUS_DISEWA,
        ]);
        $pelangganKedua = $this->createPelanggan([
            'nomor_telepon' => '081234567891',
        ]);
        $transaksiMenunggu = $this->createTransaksi($kendaraan, [
            'pelanggan_id' => $pelangganKedua->id,
            'status' => Transaksi::STATUS_MENUNGGU,
            'tanggal_mulai' => today()->addDays(3)->toDateString(),
            'tanggal_selesai' => today()->addDays(5)->toDateString(),
        ]);

        $response = $this->patch(route('transaksi.status', $transaksiMenunggu), [
            'status' => Transaksi::STATUS_DIBATALKAN,
        ]);

        $response->assertRedirect(route('transaksi.index'));
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksiDisewa->id,
            'status' => Transaksi::STATUS_DISEWA,
        ]);
        $this->assertDatabaseHas('kendaraans', [
            'id' => $kendaraan->id,
            'status' => 'Disewa',
        ]);
        $this->assertVehicleStatusOnDashboardAndLanding(
            $kendaraan,
            'Disewa',
            'bg-blue-50 text-blue-700',
            'sr-status--unavailable',
        );
    }

    public function test_admin_cannot_start_a_rental_before_confirming_it(): void
    {
        $this->actingAs(User::factory()->create());
        $kendaraan = $this->createKendaraan(['status' => 'Dipesan']);
        $transaksi = $this->createTransaksi($kendaraan);

        $response = $this->from(route('transaksi.index'))
            ->patch(route('transaksi.status', $transaksi), [
                'status' => Transaksi::STATUS_DISEWA,
            ]);

        $response->assertRedirect(route('transaksi.index'))
            ->assertSessionHasErrors('status');
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'status' => Transaksi::STATUS_MENUNGGU,
        ]);
    }

    public function test_guests_are_redirected_from_customer_and_rental_management_pages(): void
    {
        $this->get(route('pelanggan.index'))->assertRedirect(route('login'));
        $this->get(route('transaksi.index'))->assertRedirect(route('login'));
        $this->post(route('notifications.read', 'notification-id'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_download_a_customers_private_identity_document(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ktp/document.jpg', 'private-document');
        $pelanggan = $this->createPelanggan(['ktp_path' => 'ktp/document.jpg']);

        $response = $this->get(route('pelanggan.ktp', $pelanggan));

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_download_a_customers_private_identity_document(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('ktp/document.jpg', 'private-document');
        $pelanggan = $this->createPelanggan(['ktp_path' => 'ktp/document.jpg']);
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('pelanggan.ktp', $pelanggan));

        $response->assertDownload('ktp-pelanggan-'.$pelanggan->id.'.jpg');
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

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPelanggan(array $attributes = []): Pelanggan
    {
        return Pelanggan::create(array_merge([
            'nama' => 'Dewi Lestari',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Melati No. 5',
            'ktp_path' => 'ktp/dewi.jpg',
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTransaksi(Kendaraan $kendaraan, array $attributes = []): Transaksi
    {
        $pelangganId = $attributes['pelanggan_id'] ?? $this->createPelanggan()->id;

        return Transaksi::create(array_merge([
            'pelanggan_id' => $pelangganId,
            'kendaraan_id' => $kendaraan->id,
            'nama_kendaraan' => $kendaraan->nama_kendaraan,
            'plat_nomor' => $kendaraan->plat_nomor,
            'tarif_harian' => $kendaraan->tarif,
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'durasi_hari' => 1,
            'total_tarif' => $kendaraan->tarif,
            'status' => Transaksi::STATUS_MENUNGGU,
        ], $attributes));
    }

    private function assertVehicleStatusOnDashboardAndLanding(
        Kendaraan $kendaraan,
        string $status,
        string $dashboardBadgeClass,
        string $landingBadgeClass,
    ): void {
        $dashboardResponse = $this->get(route('dashboard'));
        $landingResponse = $this->get('/');

        $dashboardResponse->assertOk()
            ->assertSee($kendaraan->nama_kendaraan)
            ->assertSee($dashboardBadgeClass)
            ->assertSee($status);
        $landingResponse->assertOk()
            ->assertSee($kendaraan->nama_kendaraan)
            ->assertSee($landingBadgeClass)
            ->assertSee($status);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validBookingData(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Dewi Lestari',
            'nomor_telepon' => '081234567890',
            'alamat' => 'Jl. Melati No. 5',
            'tanggal_mulai' => now()->addDay()->toDateString(),
            'durasi_hari' => 3,
            'ktp' => UploadedFile::fake()->createWithContent('ktp.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWQAAAAASUVORK5CYII=')),
        ], $overrides);
    }
}
