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
Schema::create('pendapatan_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pendapatan_id')->constrained()->onDelete('cascade');
    $table->foreignId('menu_id')->constrained()->onDelete('cascade');
    $table->enum('sesi', ['pagi', 'siang', 'sore']);
    $table->integer('jumlah');
    $table->integer('total');
    $table->text('keterangan')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendapatan_details');
    }
};
