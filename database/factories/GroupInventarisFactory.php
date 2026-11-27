<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupInventaris>
 */
class GroupInventarisFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(['Teknologi', 'Otomotif', 'Bahan']),
            'image' => 'inventaris_images/test-group.jpg',
        ];
    }
}
