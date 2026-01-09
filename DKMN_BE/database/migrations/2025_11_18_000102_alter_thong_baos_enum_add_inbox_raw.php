<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('thong_baos')) {
            return;
        }

        // SQLite không hỗ trợ ALTER ENUM theo cách này
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Use raw SQL to avoid doctrine/dbal requirement when altering ENUM
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE thong_baos MODIFY loai ENUM('info','warning','success','error','trip_update','inbox') DEFAULT 'info'");
        } elseif ($driver === 'pgsql') {
            // Drop the old constraint if it exists (Laravel naming convention: table_column_check)
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

        // Revert to the previous set (without 'inbox')
        DB::statement("
            ALTER TABLE thong_baos
            MODIFY loai ENUM('info','warning','success','error','trip_update') DEFAULT 'info'
        ");
    }
};
