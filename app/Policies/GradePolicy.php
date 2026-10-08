<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function view(User $user, Grade $grade): bool
    {
        return $user->role === 'admin'
            || $grade->submission->assignment->course->lecturer_id === $user->id
            || $grade->submission->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen'], true);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->role === 'admin'
            || $grade->submission->assignment->course->lecturer_id === $user->id;
    }

    public function delete(User $user, Grade $grade): bool
    {
        return $user->role === 'admin';
    }
}