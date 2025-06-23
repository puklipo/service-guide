<?php

namespace Tests\Unit\Models;

use App\Models\Area;
use App\Models\Facility;
use App\Models\Pref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_can_be_created(): void
    {
        $pref = Pref::factory()->create();
        $area = Area::factory()->create([
            'name' => '渋谷区',
            'address' => '東京都渋谷区',
            'pref_id' => $pref->id,
        ]);

        $this->assertDatabaseHas('areas', [
            'name' => '渋谷区',
            'address' => '東京都渋谷区',
            'pref_id' => $pref->id,
        ]);

        $this->assertEquals('渋谷区', $area->name);
        $this->assertEquals('東京都渋谷区', $area->address);
    }

    public function test_area_belongs_to_pref(): void
    {
        $pref = Pref::factory()->create(['name' => '東京都']);
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $this->assertEquals($pref->id, $area->pref->id);
        $this->assertEquals('東京都', $area->pref->name);
    }

    public function test_area_has_many_facilities(): void
    {
        $area = Area::factory()->create();
        $facility1 = Facility::factory()->create(['area_id' => $area->id]);
        $facility2 = Facility::factory()->create(['area_id' => $area->id]);

        $this->assertCount(2, $area->facilities);
        $this->assertTrue($area->facilities->contains($facility1));
        $this->assertTrue($area->facilities->contains($facility2));
    }

    public function test_area_fillable_attributes(): void
    {
        $area = new Area;

        $expectedFillable = ['name', 'address'];
        $this->assertEquals($expectedFillable, $area->getFillable());
    }

    public function test_area_factory_creates_valid_area(): void
    {
        $area = Area::factory()->create();

        $this->assertNotNull($area->name);
        $this->assertNotNull($area->address);
        $this->assertNotNull($area->pref_id);
        $this->assertIsString($area->name);
        $this->assertIsString($area->address);
    }
}
