<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Cappuccino specialty',
            'Cornetto crema',
            'Focaccia genovese',
            'Toast bakery',
            'Succo fresco',
            'Crostatina frutti rossi',
        ]).' '.fake()->unique()->numberBetween(10, 999);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'category' => fake()->randomElement(['Caffetteria', 'Bakery', 'Salato', 'Dolci', 'Bevande']),
            'description' => fake()->sentence(14),
            'price' => fake()->randomFloat(2, 1.5, 9.5),
            'is_available' => fake()->boolean(85),
            'is_active' => true,
        ];
    }
}
