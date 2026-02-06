<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->words(3, true),
            'buy_price' => $this->faker->numberBetween(1000, 50000),
            'sell_price' => $this->faker->numberBetween(2000, 60000),
            'stock' => $this->faker->numberBetween(0, 100),
            'stock_threshold' => 5,
            'image_url' => null, // or fake image url
            'is_active' => true,
        ];
    }
}
