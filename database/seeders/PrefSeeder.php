<?php

namespace Database\Seeders;

use App\Models\Pref;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrefSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(config('pref'))->each(fn ($pref, $key) => Pref::updateOrCreate([
            'key' => $key,
        ], [
            'name' => $pref['name'],
        ]));
    }
}
