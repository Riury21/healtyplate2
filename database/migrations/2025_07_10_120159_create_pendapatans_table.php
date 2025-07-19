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
Schema::create('pendapatans', function (Blueprint $table) {
    $table->id();
    $table->string('nama'); // Nama Customer
    $table->date('tanggal');
    $table->boolean('pakai_diskon')->default(false);
    $table->boolean('pakai_ongkir')->default(false);
    $table->integer('ongkir_pagi')->default(0);
    $table->integer('ongkir_siang')->default(0);
    $table->integer('ongkir_sore')->default(0);
    $table->integer('total')->default(0);
    $table->text('keterangan')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendapatans');
    }
};
