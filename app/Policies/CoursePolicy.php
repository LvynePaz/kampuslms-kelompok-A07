<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Semua user login boleh melihat index (disaring di query).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Boleh melihat mata kuliah jika: Admin, Dosen pengampu, atau Mahasiswa terdaftar.
     */
    public function view(User $user, Course $course): bool
    {
        return $user->role === 'admin'
            || $course->lecturer_id === $user->id
            || $course->students()->whereKey($user->id)->exists();
    }

    /**
     * Hanya Admin atau Dosen pengampu yang boleh mengedit.
     */
    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin' 
            || $course->lecturer_id === $user->id;
    }

    /**
     * Hanya Admin yang boleh membuat mata kuliah baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang boleh menghapus mata kuliah.
     */
    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin';
    }
}
