<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained()->restrictOnDelete();
            $table->foreignId('kendaraan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_kendaraan');
            $table->string('plat_nomor');
            $table->unsignedInteger('tarif_harian');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->unsignedSmallInteger('durasi_hari');
            $table->unsignedBigInteger('total_tarif');
            $table->string('status')->default('Menunggu Konfirmasi');
            $table->timestamps();

            $table->index(['kendaraan_id', 'tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
