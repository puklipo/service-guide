<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Pref;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->city(),
            'address' => $this->faker->address(),
            'pref_id' => Pref::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
