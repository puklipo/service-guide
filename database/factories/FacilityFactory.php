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
            'name_kana' => 'あいうえお',  // Fixed hiragana for name_kana
            'address' => $this->faker->address(),
            'tel' => $this->faker->phoneNumber(),
            'url' => $this->faker->url(),
            'no' => $this->faker->numerify('###'),
            'pref_id' => Pref::inRandomOrder()->first()?->id ?? Pref::factory()->create()->id,
            'area_id' => Area::inRandomOrder()->first()?->id ?? Area::factory()->create()->id,
            'company_id' => Company::inRandomOrder()->first()?->id ?? Company::factory()->create()->id,
            'service_id' => Service::inRandomOrder()->first()?->id ?? Service::factory()->create()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
