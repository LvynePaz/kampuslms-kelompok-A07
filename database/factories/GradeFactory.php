<?php

namespace Database\Factories;

use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'graded_by' => fn (array $attrs) => Submission::find($attrs['submission_id'])
                ->assignment->created_by,
            'score' => fake()->randomFloat(2, 60, 100),
            'feedback' => fake()->optional()->sentence(),
            'graded_at' => now(),
        ];
    }
}
