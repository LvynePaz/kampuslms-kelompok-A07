<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur Diferensiasi No. 8: Impor/ekspor data mahasiswa & nilai (CSV/XLSX).
     * Menambahkan tabel baru grade_exports tanpa mengubah 7 tabel inti skema Bagian 4.
     */
    public function up(): void
    {
        Schema::create('grade_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['export', 'import'])->default('export');
            $table->string('filename');
            $table->unsignedInteger('row_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_exports');
    }
};

