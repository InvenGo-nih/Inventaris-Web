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
        Schema::table('borrows', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['user_id']);
            // Kemudian baru hapus kolom
            $table->dropColumn('user_id');
            $table->string('borrow_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable();
            $table->dropColumn('borrow_by');
            // Tambahkan kembali foreign key constraint
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
