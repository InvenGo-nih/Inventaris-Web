<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            if (! $this->hasIndex('inventaris', ['group_inventaris_id', 'deleted_at'])) {
                $table->index(['group_inventaris_id', 'deleted_at'], 'inv_group_deleted_idx');
            }

            if (! $this->hasIndex('inventaris', ['condition', 'status'])) {
                $table->index(['condition', 'status'], 'inv_condition_status_idx');
            }

            if (! $this->hasIndex('inventaris', ['type'])) {
                $table->index('type', 'inv_type_idx');
            }

            if (! $this->hasIndex('inventaris', ['name'])) {
                $table->index('name', 'inv_name_idx');
            }

            if (! $this->hasIndex('inventaris', ['deleted_at'])) {
                $table->index('deleted_at', 'inv_deleted_at_idx');
            }
        });

        Schema::table('borrows', function (Blueprint $table) {
            if (! $this->hasIndex('borrows', ['status', 'max_return_date', 'deleted_at'])) {
                $table->index(['status', 'max_return_date', 'deleted_at'], 'bor_status_due_deleted_idx');
            }

            if (! $this->hasIndex('borrows', ['borrow_by'])) {
                $table->index('borrow_by', 'bor_borrow_by_idx');
            }

            if (! $this->hasIndex('borrows', ['date_borrow', 'date_back'])) {
                $table->index(['date_borrow', 'date_back'], 'bor_dates_idx');
            }

            if (! $this->hasIndex('borrows', ['deleted_at'])) {
                $table->index('deleted_at', 'bor_deleted_at_idx');
            }
        });

        Schema::table('inventaris_locations', function (Blueprint $table) {
            if (! $this->hasIndex('inventaris_locations', ['location'])) {
                $table->index('location', 'inv_loc_location_idx');
            }

            if (! $this->hasIndex('inventaris_locations', ['deleted_at'])) {
                $table->index('deleted_at', 'inv_loc_deleted_at_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropIndex('inv_group_deleted_idx');
            $table->dropIndex('inv_condition_status_idx');
            $table->dropIndex('inv_type_idx');
            $table->dropIndex('inv_name_idx');
            $table->dropIndex('inv_deleted_at_idx');
        });

        Schema::table('borrows', function (Blueprint $table) {
            $table->dropIndex('bor_status_due_deleted_idx');
            $table->dropIndex('bor_borrow_by_idx');
            $table->dropIndex('bor_dates_idx');
            $table->dropIndex('bor_deleted_at_idx');
        });

        Schema::table('inventaris_locations', function (Blueprint $table) {
            $table->dropIndex('inv_loc_location_idx');
            $table->dropIndex('inv_loc_deleted_at_idx');
        });
    }

    private function hasIndex(string $table, array $columns): bool
    {
        return Schema::hasIndex($table, $columns);
    }
};
