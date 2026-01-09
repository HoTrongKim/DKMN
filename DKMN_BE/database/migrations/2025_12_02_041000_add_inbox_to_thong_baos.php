<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the enum column to include 'inbox'
        // Since Laravel doesn't support changing enum values easily with Schema builder for existing columns without doctrine/dbal and it's tricky with enums,
        // raw SQL is often safer for MySQL.
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE thong_baos MODIFY COLUMN loai ENUM('info','warning','success','error','trip_update','inbox') DEFAULT 'info'");
        } elseif ($driver === 'pgsql') {
             // Drop the old constraint if it exists
            DB::statement("ALTER TABLE thong_baos DROP CONSTRAINT IF EXISTS thong_baos_loai_check");
            // Add new constraint with 'inbox'
            DB::statement("ALTER TABLE thong_baos ADD CONSTRAINT thong_baos_loai_check CHECK (loai::text IN ('info', 'warning', 'success', 'error', 'trip_update', 'inbox'))");
        }
    }

    public function down(): void
    {
        // Revert (optional, but good practice)
        DB::statement("ALTER TABLE thong_baos MODIFY COLUMN loai ENUM('info','warning','success','error','trip_update') DEFAULT 'info'");
    }
};
