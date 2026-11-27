<?php

namespace Database\Factories;

use App\Models\GroupInventaris;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventaris>
 */
class InventarisFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_inventaris_id' => GroupInventaris::factory(),
            'name' => fake()->words(3, true),
            'specification' => fake()->sentence(),
            'serial_number' => fake()->unique()->bothify('SN-####-????'),
            'type' => fake()->randomElement(['Teknologi', 'Otomotif', 'Bahan']),
            'quantity' => 1,
            'condition' => 'Normal',
            'status' => null,
            'location' => fake()->word(),
            'image' => 'inventaris_images/test.jpg',
            'broken_description' => null,
            'is_borrow' => false,
            'qr_link' => null,
        ];
    }
}
