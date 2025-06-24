<?php

namespace Tests\Unit\Models;

use App\Models\Area;
use App\Models\Facility;
use App\Models\Pref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrefTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_pref_can_be_created(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();

        $this->assertDatabaseHas('prefs', [
            'name' => '東京都',
        ]);

        $this->assertEquals('東京都', $pref->name);
    }

    public function test_pref_has_many_facilities(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $facility1 = Facility::factory()->create(['pref_id' => $pref->id]);
        $facility2 = Facility::factory()->create(['pref_id' => $pref->id]);

        $this->assertCount(2, $pref->facilities);
        $this->assertTrue($pref->facilities->contains($facility1));
        $this->assertTrue($pref->facilities->contains($facility2));
    }

    public function test_pref_has_many_areas(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area1 = Area::factory()->create(['pref_id' => $pref->id]);
        $area2 = Area::factory()->create(['pref_id' => $pref->id]);

        $this->assertCount(2, $pref->areas);
        $this->assertTrue($pref->areas->contains($area1));
        $this->assertTrue($pref->areas->contains($area2));
    }

    public function test_pref_factory_creates_valid_pref(): void
    {
        $pref = Pref::factory()->create();

        $this->assertNotNull($pref->name);
        $this->assertIsString($pref->name);
    }
}
