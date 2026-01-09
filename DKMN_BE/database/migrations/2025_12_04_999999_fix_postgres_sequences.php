<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'chuyen_dis',
            'ghes',
            'nguoi_dungs',
            'don_hangs',
            'thanh_toans',
            'chi_tiet_don_hangs',
            'tickets',
            'payments',
            'danh_gias',
            'phan_hois',
            'thong_baos',
            'nhat_ky_hoat_dongs',
            'nguoi_dung_quyen_hans',
            'phi_dich_vus',
            'chi_tiet_phi_don_hangs',
            'cau_hinh_he_thongs',
            'thong_ke_doanh_thus',
            'huy_ves',
            'lien_hes'
        ];

        foreach ($tables as $table) {
            // Check if table exists to avoid errors
            $tableExists = DB::select("SELECT to_regclass('public.{$table}')");
            
            if (!empty($tableExists[0]->to_regclass)) {
                // Reset sequence to MAX(id) + 1
                // This ensures the next insert will use an ID greater than any existing ID
                DB::statement("
                    DO $$
                    DECLARE
                        max_id INT;
                        seq_name TEXT;
                    BEGIN
                        -- Get the sequence name associated with the id column
                        seq_name := pg_get_serial_sequence('{$table}', 'id');
                        
                        IF seq_name IS NOT NULL THEN
                            -- Get the maximum id from the table
                            EXECUTE 'SELECT COALESCE(MAX(id), 0) FROM {$table}' INTO max_id;
                            
                            -- Set the sequence to the max_id + 1 (so next val is max_id + 1)
                            -- Using setval(seq, val, false) sets the 'is_called' flag to false, 
                            -- meaning the next nextval() will return 'val'.
                            PERFORM setval(seq_name, max_id + 1, false);
                        END IF;
                    END $$;
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse sequence fix
    }
};
