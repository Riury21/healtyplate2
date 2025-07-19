<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('jenis_pembelian');
            $table->string('transaksi');
            $table->enum('jenis_transaksi', ['debit', 'kredit']);
            $table->float('kuantiti')->default(1);
            $table->bigInteger('jumlah');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes(); // jika kamu pakai soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
