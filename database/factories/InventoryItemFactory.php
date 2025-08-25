<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $units = ['piece', 'box', 'kg', 'liter', 'meter'];

        return [
            'sku' => strtoupper(fake()->unique()->lexify('???-####')),
            'name' => fake()->word(),
            'description' => fake()->sentence(10),
            'price' => fake()->randomFloat(2, 10, 1000),
            'cost' => fake()->randomFloat(2, 5, 500),
            'category_id' => fake()->numberBetween(1, 5),
            'unit_of_measure' => fake()->randomElement($units),
            'is_active' => true,
        ];
    }
}
