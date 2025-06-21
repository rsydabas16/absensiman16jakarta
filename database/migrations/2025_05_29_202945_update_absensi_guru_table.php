<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update enum status
        DB::statement("ALTER TABLE absensi_guru MODIFY COLUMN status ENUM('hadir', 'tidak_hadir', 'izin', 'sakit', 'dinas_luar', 'cuti')");
        
        // Add new column for auto alfa
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->boolean('is_auto_alfa')->default(false)->after('qr_code');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->dropColumn('is_auto_alfa');
        });
        
        DB::statement("ALTER TABLE absensi_guru MODIFY COLUMN status ENUM('hadir', 'tidak_hadir', 'izin', 'sakit')");
    }
};