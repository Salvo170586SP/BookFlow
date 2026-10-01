<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'          => rtrim(fake()->sentence(3), '.'),
            'isbn'           => fake()->unique()->isbn13(),
            'description'    => fake()->optional()->paragraph(),
            'published_year' => fake()->numberBetween(1950, 2025),
            'publisher_id'   => Publisher::factory(),
            'category_id'    => Category::factory(),
        ];
    }
}
