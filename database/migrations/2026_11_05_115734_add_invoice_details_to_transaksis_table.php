<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->string('jenis_kendaraan')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_access_token_hash')->nullable();
        });

        DB::table('transaksis')
            ->leftJoin('kendaraans', 'transaksis.kendaraan_id', '=', 'kendaraans.id')
            ->select('transaksis.id', 'transaksis.created_at', 'kendaraans.jenis')
            ->orderBy('transaksis.id')
            ->chunkById(100, function ($transaksis): void {
                foreach ($transaksis as $transaksi) {
                    $tanggalInvoice = $transaksi->created_at
                        ? Carbon::parse($transaksi->created_at)->format('Ymd')
                        : now()->format('Ymd');

                    DB::table('transaksis')
                        ->where('id', $transaksi->id)
                        ->update([
                            'jenis_kendaraan' => $transaksi->jenis ?? 'Tidak diketahui',
                            'invoice_number' => 'SWR-'.$tanggalInvoice.'-'.Str::upper((string) Str::uuid()),
                        ]);
                }
            }, 'transaksis.id', 'id');

        Schema::table('transaksis', function (Blueprint $table) {
            $table->string('jenis_kendaraan')->default('Tidak diketahui')->change();
            $table->string('invoice_number')->nullable(false)->change();
            $table->unique('invoice_number');
            $table->unique('invoice_access_token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            $table->dropUnique('transaksis_invoice_number_unique');
            $table->dropUnique('transaksis_invoice_access_token_hash_unique');
            $table->dropColumn([
                'jenis_kendaraan',
                'invoice_number',
                'invoice_access_token_hash',
            ]);
        });
    }
};
