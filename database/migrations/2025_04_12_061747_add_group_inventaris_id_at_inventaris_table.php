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
        Schema::table('inventaris', function (Blueprint $table) {
            $table->unsignedBigInteger('group_inventaris_id')->nullable()->after('id');

            $table->foreign('group_inventaris_id')
                  ->references('id')
                  ->on('group_inventaris')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropForeign(['group_inventaris_id']);
            $table->dropColumn('group_inventaris_id');
        });
    }
};
