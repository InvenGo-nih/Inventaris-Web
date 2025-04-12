<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->string('serial_number')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropColumn('serial_number');
        });
    }
}; 