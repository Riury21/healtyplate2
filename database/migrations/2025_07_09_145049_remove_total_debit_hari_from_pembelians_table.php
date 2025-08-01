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
    Schema::table('pembelians', function (Blueprint $table) {
        $table->dropColumn('total_debit_hari');
    });
}

public function down(): void
{
    Schema::table('pembelians', function (Blueprint $table) {
        $table->double('total_debit_hari')->nullable();
    });
}

};
