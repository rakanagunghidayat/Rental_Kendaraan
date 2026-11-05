<?php

namespace Tests\Feature;

use App\Models\Kendaraan;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invoiceStatuses')]
    public function test_public_invoice_uses_the_saved_transaction_status(
        string $status,
        string $documentTitle,
        string $invoiceStatus,
    ): void {
        $token = str_repeat('T', 64);
        $transaksi = $this->createTransaksi($token, ['status' => $status]);

        $response = $this->get(route('invoice.public', $token));

        $response->assertOk()
            ->assertSee($documentTitle)
            ->assertSee($invoiceStatus)
            ->assertSee($transaksi->invoice_number);

        if ($status === Transaksi::STATUS_MENUNGGU) {
            $response->assertSee('Cetak Bukti Permintaan');
        } else {
            $response->assertSee('Cetak Invoice');
        }
    }

    public static function invoiceStatuses(): array
    {
        return [
            'waiting for confirmation' => [
                Transaksi::STATUS_MENUNGGU,
                'Bukti Permintaan Sewa',
                'Menunggu Konfirmasi',
            ],
            'confirmed' => [
                Transaksi::STATUS_DIPESAN,
                'Invoice Pemesanan',
                'Dikonfirmasi',
            ],
            'being rented' => [
                Transaksi::STATUS_DISEWA,
                'Invoice Pemesanan',
                'Sedang Disewa',
            ],
            'completed' => [
                Transaksi::STATUS_SELESAI,
                'Invoice Pemesanan',
                'Selesai',
            ],
            'cancelled' => [
                Transaksi::STATUS_DIBATALKAN,
                'Invoice Pemesanan',
                'Dibatalkan',
            ],
        ];
    }

    public function test_public_invoice_displays_saved_rental_data_without_identity_document_and_keeps_its_number(): void
    {
        $token = str_repeat('A', 64);
        $transaksi = $this->createTransaksi($token, [
            'status' => Transaksi::STATUS_DISEWA,
        ]);

        $firstResponse = $this->get(route('invoice.public', $token));
        $secondResponse = $this->get(route('invoice.public', $token));

        foreach ([$firstResponse, $secondResponse] as $response) {
            $response->assertOk()
                ->assertSee('Dewi Lestari')
                ->assertSee('081234567890')
                ->assertSee('Jl. Melati No. 5')
                ->assertSee('Honda Brio')
                ->assertSee('Mobil')
                ->assertSee('08/11/2026')
                ->assertSee('09/11/2026')
                ->assertSee('2 hari')
                ->assertSee('Rp 200.000')
                ->assertSee('Rp 400.000')
                ->assertSee('Cetak Invoice')
                ->assertSee('Unduh PDF')
                ->assertSee('bukan bukti pembayaran')
                ->assertDontSee('ktp/private-identity.jpg')
                ->assertDontSee('invoice_access_token_hash')
                ->assertHeader('Referrer-Policy', 'no-referrer')
                ->assertHeader('Cache-Control', 'no-store, private');
        }

        $this->assertSame($firstResponse->getContent(), $secondResponse->getContent());
        $this->assertDatabaseCount('transaksis', 1);
        $this->assertDatabaseHas('transaksis', [
            'id' => $transaksi->id,
            'invoice_number' => $transaksi->invoice_number,
            'jenis_kendaraan' => 'Mobil',
        ]);
    }

    public function test_unknown_public_invoice_token_returns_not_found(): void
    {
        $response = $this->get(route('invoice.public', str_repeat('Z', 64)));

        $response->assertNotFound();
    }

    public function test_admin_invoice_requires_authentication_and_is_available_to_dashboard_users(): void
    {
        $transaksi = $this->createTransaksi(str_repeat('B', 64));

        $this->get(route('transaksi.invoice', $transaksi))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('transaksi.invoice', $transaksi))
            ->assertOk()
            ->assertSee($transaksi->invoice_number);
    }

    public function test_each_transaction_receives_a_distinct_invoice_number(): void
    {
        $firstTransaction = $this->createTransaksi(str_repeat('C', 64));
        $secondTransaction = $this->createTransaksi(str_repeat('D', 64), [
            'customer_phone' => '081234567891',
        ]);

        $this->assertNotSame($firstTransaction->invoice_number, $secondTransaction->invoice_number);
        $this->assertDatabaseCount('transaksis', 2);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createTransaksi(string $token, array $attributes = []): Transaksi
    {
        $customer = Pelanggan::create([
            'nama' => 'Dewi Lestari',
            'nomor_telepon' => $attributes['customer_phone'] ?? '081234567890',
            'alamat' => 'Jl. Melati No. 5',
            'ktp_path' => 'ktp/private-identity.jpg',
        ]);
        $kendaraan = Kendaraan::create([
            'nama_kendaraan' => 'Honda Brio',
            'jenis' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif' => 200000,
            'status' => 'Tersedia',
            'foto' => [],
        ]);

        unset($attributes['customer_phone']);

        return Transaksi::create(array_merge([
            'pelanggan_id' => $customer->id,
            'kendaraan_id' => $kendaraan->id,
            'nama_kendaraan' => 'Honda Brio',
            'jenis_kendaraan' => 'Mobil',
            'plat_nomor' => 'B 1234 CD',
            'tarif_harian' => 200000,
            'tanggal_mulai' => '2026-11-08',
            'tanggal_selesai' => '2026-11-10',
            'durasi_hari' => 2,
            'total_tarif' => 400000,
            'status' => Transaksi::STATUS_MENUNGGU,
            'invoice_access_token_hash' => hash('sha256', $token),
        ], $attributes));
    }
}
