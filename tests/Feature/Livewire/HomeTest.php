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
            ->assertSee('施設')
            ->assertViewIs('livewire.home');
    }

    public function test_facilities_computed_property_returns_paginated_results(): void
    {
        Facility::factory()->count(5)->create();

        Livewire::test(Home::class)
            ->assertOk()
            ->call('facilities')
            ->assertCount('facilities.items', 5);
    }

    public function test_facilities_can_be_filtered_by_service(): void
    {
        $service1 = Service::find(11); // 居宅介護
        $service2 = Service::find(12); // 重度訪問介護

        Facility::factory()->count(3)->create(['service_id' => $service1->id]);
        Facility::factory()->count(2)->create(['service_id' => $service2->id]);

        Livewire::test(Home::class)
            ->set('service', $service1->id)
            ->call('facilities')
            ->assertCount('facilities.items', 3);

        Livewire::test(Home::class)
            ->set('service', $service2->id)
            ->call('facilities')
            ->assertCount('facilities.items', 2);
    }

    public function test_facilities_can_be_filtered_by_pref(): void
    {
        $pref1 = Pref::where('key', 'tokyo')->first(); // 東京都
        $pref2 = Pref::where('key', 'osaka')->first(); // 大阪府

        Facility::factory()->count(3)->create(['pref_id' => $pref1->id]);
        Facility::factory()->count(2)->create(['pref_id' => $pref2->id]);

        Livewire::test(Home::class)
            ->set('pref', $pref1->id)
            ->call('facilities')
            ->assertCount('facilities.items', 3);

        Livewire::test(Home::class)
            ->set('pref', $pref2->id)
            ->call('facilities')
            ->assertCount('facilities.items', 2);
    }

    public function test_facilities_can_be_filtered_by_area(): void
    {
        $area1 = Area::factory()->create();
        $area2 = Area::factory()->create();

        Facility::factory()->count(3)->create(['area_id' => $area1->id]);
        Facility::factory()->count(2)->create(['area_id' => $area2->id]);

        Livewire::test(Home::class)
            ->set('area', $area1->id)
            ->call('facilities')
            ->assertCount('facilities.items', 3);

        Livewire::test(Home::class)
            ->set('area', $area2->id)
            ->call('facilities')
            ->assertCount('facilities.items', 2);
    }

    public function test_prefs_computed_property_returns_prefs_with_facility_count(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        Facility::factory()->count(3)->create(['pref_id' => $pref->id]);

        Livewire::test(Home::class)
            ->assertOk()
            ->call('prefs')
            ->assertSet('prefs.0.facilities_count', 3);
    }

    public function test_areas_computed_property_returns_empty_when_no_pref_selected(): void
    {
        Livewire::test(Home::class)
            ->assertOk()
            ->call('areas')
            ->assertCount('areas', 0);
    }

    public function test_areas_computed_property_returns_areas_for_selected_pref(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area1 = Area::factory()->create(['pref_id' => $pref->id]);
        $area2 = Area::factory()->create(['pref_id' => $pref->id]);

        // Create facilities to test ordering by facility count
        Facility::factory()->count(5)->create(['area_id' => $area1->id]);
        Facility::factory()->count(3)->create(['area_id' => $area2->id]);

        Livewire::test(Home::class)
            ->set('pref', $pref->id)
            ->call('areas')
            ->assertCount('areas', 2)
            ->assertSet('areas.0.id', $area1->id) // Should be first due to higher facility count
            ->assertSet('areas.0.facilities_count', 5)
            ->assertSet('areas.1.facilities_count', 3);
    }

    public function test_services_computed_property_returns_services_with_facilities(): void
    {
        $service1 = Service::find(11); // 居宅介護
        $service2 = Service::find(12); // 重度訪問介護
        $service3 = Service::find(13); // 行動援護 (Service with no facilities)

        Facility::factory()->count(5)->create(['service_id' => $service1->id]);
        Facility::factory()->count(3)->create(['service_id' => $service2->id]);

        Livewire::test(Home::class)
            ->assertOk()
            ->call('services')
            ->assertCount('services', 2) // Only services with facilities
            ->assertSet('services.0.id', $service1->id) // Should be first due to higher facility count
            ->assertSet('services.0.facilities_count', 5);
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
            ->assertHasErrors('service')
            ->set('limit', 'invalid')
            ->assertHasErrors('limit');
    }

    public function test_title_generation_with_filters(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['name' => '渋谷区']);
        $service = Service::find(11); // 居宅介護

        $component = Livewire::test(Home::class)
            ->set('pref', $pref->id)
            ->set('area', $area->id)
            ->set('service', $service->id);

        $title = $component->instance()->render()->title();

        $this->assertStringContainsString('東京都', $title);
        $this->assertStringContainsString('渋谷区', $title);
        $this->assertStringContainsString('居宅介護', $title);
        $this->assertStringContainsString(config('app.name'), $title);
    }

    public function test_pagination_limit_is_respected(): void
    {
        Facility::factory()->count(150)->create();

        Livewire::test(Home::class)
            ->set('limit', 50)
            ->call('facilities')
            ->assertCount('facilities.items', 50);
    }

    public function test_combined_filters_work_together(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['pref_id' => $pref->id]);
        $service = Service::find(11); // 居宅介護

        // Facilities that match all criteria
        Facility::factory()->count(2)->create([
            'pref_id' => $pref->id,
            'area_id' => $area->id,
            'service_id' => $service->id,
        ]);

        // Facilities that don't match all criteria
        Facility::factory()->count(3)->create(['pref_id' => $pref->id]);
        Facility::factory()->count(1)->create(['service_id' => $service->id]);

        Livewire::test(Home::class)
            ->set('pref', $pref->id)
            ->set('area', $area->id)
            ->set('service', $service->id)
            ->call('facilities')
            ->assertCount('facilities.items', 2);
    }
}
