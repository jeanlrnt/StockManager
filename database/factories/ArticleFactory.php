<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'title' => $name,
            'slug' => Str::slug($name),
            'provider' => Provider::factory()->create()->id,
        ];
    }
}
