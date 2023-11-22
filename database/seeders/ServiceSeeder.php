<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(config('service'))->each(fn ($name, $id) => Service::updateOrCreate([
            'id' => $id,
        ], [
            'name' => $name,
        ]));
    }
}
