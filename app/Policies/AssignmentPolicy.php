<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Assignment $assignment): bool
    {
        return $user->role === 'admin'
            || $assignment->course->lecturer_id === $user->id
            || $assignment->course->students()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen'], true);
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $user->role === 'admin'
            || $assignment->course->lecturer_id === $user->id;
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->role === 'admin'
            || $assignment->course->lecturer_id === $user->id;
    }
}
