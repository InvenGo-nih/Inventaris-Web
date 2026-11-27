<?php

namespace Database\Factories;

use App\Models\Inventaris;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Borrow>
 */
class BorrowFactory extends Factory
{
    public function definition(): array
    {
        $dateBorrow = now()->subDays(3)->toDateString();

        return [
            'borrow_by' => fake()->name(),
            'inventaris_id' => Inventaris::factory(),
            'quantity' => 1,
            'date_borrow' => $dateBorrow,
            'date_back' => null,
            'max_return_date' => now()->addDays(4)->toDateString(),
            'status' => 'Dipinjam',
            'img_borrow' => 'borrow_images/test.jpg',
        ];
    }

    public function returned(): static
    {
        return $this->state(fn () => [
            'status' => 'Dikembalikan',
            'date_back' => now()->toDateString(),
        ]);
    }
}
