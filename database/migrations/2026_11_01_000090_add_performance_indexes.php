<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->index(['status', 'lecturer_id'], 'courses_status_lecturer_index');
        });

        Schema::table('course_user', function (Blueprint $table) {
            $table->index(['user_id', 'course_id'], 'course_user_user_course_index');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->index(['course_id', 'created_at'], 'materials_course_created_index');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->index(['user_id', 'assignment_id'], 'submissions_user_assignment_index');
        });

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
