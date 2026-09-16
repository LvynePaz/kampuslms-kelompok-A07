<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory()->mahasiswa(),
            'file_path' => 'submissions/' . fake()->uuid() . '.pdf',
            'original_name' => fake()->word() . '.pdf',
            'file_size' => fake()->numberBetween(10_000, 2_000_000),
            'note' => fake()->optional()->sentence(),
            'submitted_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'is_late' => false,
        ];
    }
}
