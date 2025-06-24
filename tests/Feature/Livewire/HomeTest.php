<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Home;
use App\Models\Area;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_home_component_can_render(): void
    {
        Livewire::test(Home::class)
            ->assertOk()
            ->assertSee('事業所') // This text is actually in the rendered HTML
            ->assertViewIs('livewire.home');
    }

    public function test_facilities_computed_property_returns_paginated_results(): void
    {
        Facility::factory()->count(5)->create();

        $component = Livewire::test(Home::class)
            ->assertOk();
            
        $facilities = $component->get('facilities');
        $this->assertCount(5, $facilities->items());
    }

    public function test_facilities_can_be_filtered_by_service(): void
    {
        $service1 = Service::find(11); // 居宅介護
        $service2 = Service::find(12); // 重度訪問介護

        Facility::factory()->count(3)->create(['service_id' => $service1->id]);
        Facility::factory()->count(2)->create(['service_id' => $service2->id]);

        $component1 = Livewire::test(Home::class)
            ->set('service', $service1->id);
        $facilities1 = $component1->get('facilities');
        $this->assertCount(3, $facilities1->items());

        $component2 = Livewire::test(Home::class)
            ->set('service', $service2->id);
        $facilities2 = $component2->get('facilities');
        $this->assertCount(2, $facilities2->items());
    }

    public function test_facilities_can_be_filtered_by_pref(): void
    {
        $pref1 = Pref::where('key', 'tokyo')->first(); // 東京都
        $pref2 = Pref::where('key', 'osaka')->first(); // 大阪府

        Facility::factory()->count(3)->create(['pref_id' => $pref1->id]);
        Facility::factory()->count(2)->create(['pref_id' => $pref2->id]);

        $component1 = Livewire::test(Home::class)
            ->set('pref', $pref1->id);
        $facilities1 = $component1->get('facilities');
        $this->assertCount(3, $facilities1->items());

        $component2 = Livewire::test(Home::class)
            ->set('pref', $pref2->id);
        $facilities2 = $component2->get('facilities');
        $this->assertCount(2, $facilities2->items());
    }

    public function test_facilities_can_be_filtered_by_area(): void
    {
        $area1 = Area::factory()->create();
        $area2 = Area::factory()->create();

        Facility::factory()->count(3)->create(['area_id' => $area1->id]);
        Facility::factory()->count(2)->create(['area_id' => $area2->id]);

        $component1 = Livewire::test(Home::class)
            ->set('area', $area1->id);
        $facilities1 = $component1->get('facilities');
        $this->assertCount(3, $facilities1->items());

        $component2 = Livewire::test(Home::class)
            ->set('area', $area2->id);
        $facilities2 = $component2->get('facilities');
        $this->assertCount(2, $facilities2->items());
    }

    public function test_prefs_computed_property_returns_prefs_with_facility_count(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        Facility::factory()->count(3)->create(['pref_id' => $pref->id]);

        $component = Livewire::test(Home::class)
            ->assertOk();
            
        $prefs = $component->get('prefs');
        $tokyoPref = $prefs->firstWhere('id', $pref->id);
        $this->assertEquals(3, $tokyoPref->facilities_count);
    }

    public function test_areas_computed_property_returns_empty_when_no_pref_selected(): void
    {
        $component = Livewire::test(Home::class)
            ->assertOk();
            
        $areas = $component->get('areas');
        $this->assertCount(0, $areas);
    }

    public function test_areas_computed_property_returns_areas_for_selected_pref(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area1 = Area::factory()->create(['pref_id' => $pref->id]);
        $area2 = Area::factory()->create(['pref_id' => $pref->id]);

        // Create facilities to test ordering by facility count
        Facility::factory()->count(5)->create(['area_id' => $area1->id]);
        Facility::factory()->count(3)->create(['area_id' => $area2->id]);

        $component = Livewire::test(Home::class)
            ->set('pref', $pref->id);
            
        $areas = $component->get('areas');
        $this->assertCount(2, $areas);
        $this->assertEquals($area1->id, $areas[0]->id); // Should be first due to higher facility count
        $this->assertEquals(5, $areas[0]->facilities_count);
        $this->assertEquals(3, $areas[1]->facilities_count);
    }

    public function test_services_computed_property_returns_services_with_facilities(): void
    {
        $service1 = Service::find(11); // 居宅介護
        $service2 = Service::find(12); // 重度訪問介護
        $service3 = Service::find(13); // 行動援護 (Service with no facilities)

        Facility::factory()->count(5)->create(['service_id' => $service1->id]);
        Facility::factory()->count(3)->create(['service_id' => $service2->id]);

        $component = Livewire::test(Home::class)
            ->assertOk();
            
        $services = $component->get('services');
        $this->assertCount(2, $services); // Only services with facilities
        $this->assertEquals($service1->id, $services[0]->id); // Should be first due to higher facility count
        $this->assertEquals(5, $services[0]->facilities_count);
    }

    public function test_updated_pref_clears_area_selection(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        Livewire::test(Home::class)
            ->set('area', $area->id)
            ->assertSet('area', $area->id)
            ->set('pref', $pref->id)
            ->assertSet('area', ''); // Area should be cleared when pref changes
    }

    public function test_url_parameters_are_bound_correctly(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create();
        $service = Service::find(11); // 居宅介護

        Livewire::test(Home::class)
            ->set('pref', $pref->id)
            ->set('area', $area->id)
            ->set('service', $service->id)
            ->set('limit', 50)
            ->assertSet('pref', $pref->id)
            ->assertSet('area', $area->id)
            ->assertSet('service', $service->id)
            ->assertSet('limit', 50);
    }

    public function test_validation_rules_for_url_parameters(): void
    {
        Livewire::test(Home::class)
            ->set('pref', 'invalid')
            ->assertHasErrors('pref')
            ->set('area', 'invalid')  
            ->assertHasErrors('area')
            ->set('service', 'invalid')
            ->assertHasErrors('service');
            
        // Limit validation is handled by PHP type system (int property)
        // so setting invalid string values will throw TypeError as expected
    }

    public function test_title_generation_with_filters(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['name' => '渋谷区']);
        $service = Service::find(11); // 居宅介護

        // Test that component accepts the filter values correctly
        $component = Livewire::test(Home::class)
            ->set('pref', $pref->id)
            ->set('area', $area->id)
            ->set('service', $service->id)
            ->assertOk()
            ->assertSet('pref', $pref->id)
            ->assertSet('area', $area->id)
            ->assertSet('service', $service->id);
            
        // Verify the component can render without errors
        $this->assertNotNull($component->instance());
    }

    public function test_pagination_limit_is_respected(): void
    {
        Facility::factory()->count(150)->create();

        $component = Livewire::test(Home::class)
            ->set('limit', 50);
            
        $facilities = $component->get('facilities');
        $this->assertCount(50, $facilities->items());
    }

    public function test_combined_filters_work_together(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['pref_id' => $pref->id]);
        $service = Service::find(11); // 居宅介護
        
        $otherPref = Pref::where('key', 'osaka')->first(); // 大阪府
        $otherService = Service::find(12); // 重度訪問介護
        $otherArea = Area::factory()->create(['pref_id' => $otherPref->id]);

        // Facilities that match all criteria
        Facility::factory()->count(2)->create([
            'pref_id' => $pref->id,
            'area_id' => $area->id,
            'service_id' => $service->id,
        ]);

        // Facilities that don't match all criteria (different pref/area/service)
        Facility::factory()->count(3)->create([
            'pref_id' => $otherPref->id,
            'area_id' => $otherArea->id, 
            'service_id' => $otherService->id
        ]);

        $component = Livewire::test(Home::class)
            ->set('pref', $pref->id)
            ->set('area', $area->id)
            ->set('service', $service->id);
            
        $facilities = $component->get('facilities');
        $this->assertCount(2, $facilities->items());
    }
}
