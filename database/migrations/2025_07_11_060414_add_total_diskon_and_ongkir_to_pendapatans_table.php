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
    Schema::table('pendapatans', function (Blueprint $table) {
        $table->integer('total_ongkir')->default(0)->after('ongkir');
        $table->integer('total_diskon')->default(0)->after('total_ongkir');
    });
}

public function down()
{
    Schema::table('pendapatans', function (Blueprint $table) {
        $table->dropColumn(['total_ongkir', 'total_diskon']);
    });
}

};
