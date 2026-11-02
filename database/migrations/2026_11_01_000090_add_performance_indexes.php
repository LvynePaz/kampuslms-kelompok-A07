<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menguntungkan query filter dashboard & course index: Course::where('status', 'active')->where('lecturer_id', $id)
        Schema::table('courses', function (Blueprint $table) {
            $table->index(['status', 'lecturer_id'], 'courses_status_lecturer_index');
        });
    
        // Menguntungkan query daftar kelas mahasiswa terdaftar: User::find($id)->courses
        Schema::table('course_user', function (Blueprint $table) {
            $table->index(['user_id', 'course_id'], 'course_user_user_course_index');
        });
    
        // Menguntungkan query pengurutan materi terbaru per kelas: Course::materials()->latest()
        Schema::table('materials', function (Blueprint $table) {
            $table->index(['course_id', 'created_at'], 'materials_course_created_index');
        });
    
        // Menguntungkan query pencarian riwayat pengumpulan tugas per mahasiswa: User::submissions()->where('assignment_id', ...)
        Schema::table('submissions', function (Blueprint $table) {
            $table->index(['user_id', 'assignment_id'], 'submissions_user_assignment_index');
        });
    
        // Menguntungkan query audit performa penilaian oleh dosen penilai: Grade::where('graded_by', $lecturerId)
        Schema::table('grades', function (Blueprint $table) {
            $table->index('graded_by', 'grades_graded_by_index');
        });
    }

    public function down(): void
    {
        Schema::table('grades', fn (Blueprint $table) => $table->dropIndex('grades_graded_by_index'));
        Schema::table('submissions', fn (Blueprint $table) => $table->dropIndex('submissions_user_assignment_index'));
        Schema::table('materials', fn (Blueprint $table) => $table->dropIndex('materials_course_created_index'));
        Schema::table('course_user', fn (Blueprint $table) => $table->dropIndex('course_user_user_course_index'));
        Schema::table('courses', fn (Blueprint $table) => $table->dropIndex('courses_status_lecturer_index'));
    }
};
