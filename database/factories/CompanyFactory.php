<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numerify('##########'),
            'name' => $this->faker->company(),
            'name_kana' => 'あいうえお',  // Fixed hiragana for name_kana
            'area' => $this->faker->city(),
            'address' => $this->faker->address(),
            'tel' => $this->faker->phoneNumber(),
            'url' => $this->faker->url(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
