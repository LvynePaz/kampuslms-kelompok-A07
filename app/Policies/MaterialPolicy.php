<?php

namespace App\Policies;

use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Material $material): bool
    {
        return $user->role === 'admin'
            || $material->course->lecturer_id === $user->id
            || $material->course->students()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen'], true);
    }

    public function update(User $user, Material $material): bool
    {
        return $user->role === 'admin'
            || $material->course->lecturer_id === $user->id;
    }

    public function delete(User $user, Material $material): bool
    {
        return $user->role === 'admin'
            || $material->course->lecturer_id === $user->id;
    }
}
