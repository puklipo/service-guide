<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Company;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    protected $model = Facility::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wam' => $this->faker->unique()->numerify('##########'),
            'name' => $this->faker->company().'施設',
            'name_kana' => $this->faker->optional()->name(),
            'address' => $this->faker->address(),
            'tel' => $this->faker->optional()->phoneNumber(),
            'url' => $this->faker->optional()->url(),
            'no' => $this->faker->optional()->numerify('###'),
            'pref_id' => Pref::inRandomOrder()->first()->id,
            'area_id' => Area::factory(),
            'company_id' => Company::factory(),
            'service_id' => Service::inRandomOrder()->first()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
