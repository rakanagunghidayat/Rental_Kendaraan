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
        Schema::create('perawatan_kendaraans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kendaraan_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('nama_kendaraan');
            $table->string('plat_nomor');
            $table->date('servis_terakhir');
            $table->date('servis_berikutnya');
            $table->string('jenis_perawatan');
            $table->text('catatan')->nullable();
            $table->string('status')->default('Normal');
            $table->timestamps();

            $table->index(['status', 'servis_berikutnya']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perawatan_kendaraans');
    }
};
