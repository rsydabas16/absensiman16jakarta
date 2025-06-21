<?php
// database/migrations/2024_01_01_000006_create_absensi_guru_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajaran')->onDelete('cascade');
            $table->date('tanggal');
            $table->time('jam_absen')->nullable();
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit']);
            $table->text('alasan')->nullable();
            $table->text('tugas')->nullable();
            $table->string('qr_code')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_guru');
    }
};