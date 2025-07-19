<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saldo_hutang_umums', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('transaksi');
            $table->enum('jenis_transaksi', ['debit', 'kredit']);
            $table->bigInteger('jumlah'); // nilai transaksi
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes(); // karena kamu minta pakai soft deletes
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_hutang_umums');
    }
};
