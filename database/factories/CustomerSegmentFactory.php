<?php

namespace Database\Factories;

use App\Models\CustomerSegment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CustomerSegment>
 */
class CustomerSegmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'color' => fake()->randomElement(['blue', 'indigo', 'purple', 'emerald', 'amber', 'rose']),
            'is_active' => true,
        ];
    }
}
