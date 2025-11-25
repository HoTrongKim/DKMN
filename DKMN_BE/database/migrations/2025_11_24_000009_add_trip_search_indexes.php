<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function indexExists(string $table, string $index): bool
    {
        $tableName = Schema::getConnection()->getTablePrefix() . $table;

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('index_name', $index)
            ->exists();
    }

    public function up(): void
    {
        if (!Schema::hasTable('chuyen_dis')) {
            return;
        }

        $hasFromCity = Schema::hasColumn('chuyen_dis', 'noi_di_tinh_thanh_id');
        $hasToCity = Schema::hasColumn('chuyen_dis', 'noi_den_tinh_thanh_id');

        if ($hasFromCity && $hasToCity && !$this->indexExists('chuyen_dis', 'idx_cd_from_to_departure')) {
            Schema::table('chuyen_dis', function (Blueprint $table) {
                $table->index(
                    ['noi_di_tinh_thanh_id', 'noi_den_tinh_thanh_id', 'gio_khoi_hanh', 'id'],
                    'idx_cd_from_to_departure'
                );
            });
        }

        if (Schema::hasColumn('chuyen_dis', 'nha_van_hanh_id') && !$this->indexExists('chuyen_dis', 'idx_cd_operator_departure')) {
            Schema::table('chuyen_dis', function (Blueprint $table) {
                $table->index(
                    ['nha_van_hanh_id', 'gio_khoi_hanh', 'id'],
                    'idx_cd_operator_departure'
                );
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('chuyen_dis')) {
            return;
        }

        if ($this->indexExists('chuyen_dis', 'idx_cd_from_to_departure')) {
            Schema::table('chuyen_dis', function (Blueprint $table) {
                $table->dropIndex('idx_cd_from_to_departure');
            });
        }

        if ($this->indexExists('chuyen_dis', 'idx_cd_operator_departure')) {
            Schema::table('chuyen_dis', function (Blueprint $table) {
                $table->dropIndex('idx_cd_operator_departure');
            });
        }
    }
};

