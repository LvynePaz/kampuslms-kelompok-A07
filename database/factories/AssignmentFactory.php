<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'created_by' => fn (array $attrs) => Course::find($attrs['course_id'])->lecturer_id,
            'title' => 'Tugas ' . fake()->words(2, true),
            'instructions' => fake()->paragraph(),
            'due_at' => fake()->dateTimeBetween('-2 weeks', '+2 weeks'),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'due_at' => fake()->dateTimeBetween('-3 weeks', '-1 week'),
            'status' => 'published',
        ]);
    }

    public function activeUpcoming(): static
    {
        return $this->state(fn () => [
            'due_at' => fake()->dateTimeBetween('+1 day', '+2 weeks'),
            'status' => 'published',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'due_at' => fake()->dateTimeBetween('+2 weeks', '+4 weeks'),
            'status' => 'draft',
        ]);
    }
}
