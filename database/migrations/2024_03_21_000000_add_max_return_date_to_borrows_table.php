<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->date('max_return_date')->nullable()->after('date_back');
        });
    }

    public function down()
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn('max_return_date');
        });
    }
}; 