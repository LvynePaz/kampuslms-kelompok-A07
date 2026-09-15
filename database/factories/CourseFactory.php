<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'SI' . fake()->unique()->numerify('#######'),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'sks' => fake()->numberBetween(2, 4),
            'lecturer_id' => User::factory()->dosen(),
            'status' => 'active',
        ];
    }
}
