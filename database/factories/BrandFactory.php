<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Product\Models\Brand;

class BrandFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Brand::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->optional()->paragraph(),
            'logo' => null,
            'website' => $this->faker->optional()->url(),
            'outlet_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(80),
        ];
    }

    /**
     * Indicate that the brand is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the brand is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the brand has a logo.
     */
    public function withLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo' => $this->faker->imageUrl(200, 200, 'business'),
        ]);
    }
}
