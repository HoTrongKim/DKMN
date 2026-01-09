<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('thong_baos')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('thong_baos', function (Blueprint $table) {
                $table->enum('loai', ['info', 'warning', 'success', 'error', 'trip_update', 'inbox'])
                    ->default('info')
                    ->change();
            });
        } elseif ($driver === 'pgsql') {
            // Drop the old constraint if it exists
            DB::statement("ALTER TABLE thong_baos DROP CONSTRAINT IF EXISTS thong_baos_loai_check");
            // Add new constraint with 'inbox'
            DB::statement("ALTER TABLE thong_baos ADD CONSTRAINT thong_baos_loai_check CHECK (loai::text IN ('info', 'warning', 'success', 'error', 'trip_update', 'inbox'))");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('thong_baos')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Convert any existing 'inbox' records back to 'info' before shrinking enum
        DB::table('thong_baos')->where('loai', 'inbox')->update(['loai' => 'info']);

        Schema::table('thong_baos', function (Blueprint $table) {
            $table->enum('loai', ['info', 'warning', 'success', 'error', 'trip_update'])
                ->default('info')
                ->change();
        });
    }
};
