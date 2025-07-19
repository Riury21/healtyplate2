<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('pendapatan_details', function (Blueprint $table) {
        $table->integer('total')->default(0)->change();
    });
}
public function down()
{
    Schema::table('pendapatan_details', function (Blueprint $table) {
        // Kembalikan ke sebelumnya jika perlu
    });
}
};
