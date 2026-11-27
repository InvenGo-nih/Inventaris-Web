<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $validRoleIds = DB::table('roles')->pluck('id')->map(fn ($id) => (int) $id)->flip();
        $modelType = (new User)->getMorphClass();

        DB::table('users')
            ->select('id', 'role_id')
            ->whereNotNull('role_id')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($validRoleIds, $modelType) {
                $rows = [];

                foreach ($users as $user) {
                    if (! $validRoleIds->has((int) $user->role_id)) {
                        continue;
                    }

                    $rows[] = [
                        'role_id' => $user->role_id,
                        'model_type' => $modelType,
                        'model_id' => $user->id,
                    ];
                }

                if ($rows !== []) {
                    DB::table('model_has_roles')->insertOrIgnore($rows);
                }
            });

        DB::table('inventaris')->whereNull('quantity')->update(['quantity' => 1]);
        DB::table('inventaris')->whereNull('is_borrow')->update(['is_borrow' => 0]);
    }

    public function down(): void
    {
        //
    }
};
