<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'dosen'], true);
    }

    /**
     * Mencegah IDOR: Mahasiswa hanya boleh melihat tugas miliknya sendiri.
     */
    public function view(User $user, Submission $submission): bool
    {
        return $user->role === 'admin'
            || $submission->assignment->course->lecturer_id === $user->id
            || $submission->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'mahasiswa';
    }

    public function update(User $user, Submission $submission): bool
    {
        // Hanya boleh diedit oleh pemiliknya jika belum dinilai
        return $submission->user_id === $user->id && ! $submission->grade()->exists();
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->role === 'admin'
            || ($submission->user_id === $user->id && ! $submission->grade()->exists());
    }
}
