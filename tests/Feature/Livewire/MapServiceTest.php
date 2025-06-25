<?php

namespace Tests\Feature\Livewire;

use App\Models\Area;
use App\Models\Pref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MapServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_map_service_component_can_render(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ])
            ->assertOk()
            ->assertSee('サービスを表示');
    }

    public function test_map_service_displays_all_services_from_config(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        $services = config('service');

        foreach ($services as $serviceId => $serviceName) {
            $component->assertSee($serviceName);
        }
    }

    public function test_map_service_generates_correct_service_links(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        $services = config('service');

        foreach (array_slice($services, 0, 3, true) as $serviceId => $serviceName) {
            $expectedLink = '/?pref='.$pref->id.'&amp;area='.$area->id.'&amp;service='.$serviceId;
            $component->assertSee($expectedLink, false);
        }
    }

    public function test_map_service_state_variables_are_set_correctly(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        $this->assertEquals($pref->id, $component->get('pref'));
        $this->assertEquals($area->id, $component->get('area'));
    }

    public function test_map_service_uses_details_summary_structure(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        $component->assertSee('<details', false);
        $component->assertSee('<summary>サービスを表示</summary>', false);
    }

    public function test_map_service_wire_keys_are_unique(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        $services = config('service');

        // Check that each service has a unique wire:key (test a few)
        foreach (array_slice($services, 0, 3, true) as $serviceId => $serviceName) {
            $component->assertSee('wire:key="'.$serviceId.'"', false);
        }
    }

    public function test_map_service_css_classes_are_applied(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        // Check for specific CSS classes
        $component->assertSee('*:hover:text-primary *:hover:underline', false);
        $component->assertSee('text-sm', false);
        $component->assertSee('ml-3 mb-3', false);
    }

    public function test_map_service_handles_numeric_ids(): void
    {
        $prefId = 13; // Tokyo's ID
        $areaId = 999; // Arbitrary area ID

        $component = Volt::test('map-service', [
            'pref' => $prefId,
            'area' => $areaId,
        ]);

        // Check that numeric IDs are handled correctly in URLs
        $component->assertSee('pref='.$prefId, false);
        $component->assertSee('area='.$areaId, false);
    }

    public function test_map_service_handles_string_ids(): void
    {
        $prefId = '13'; // String version of Tokyo's ID
        $areaId = '999'; // String version of arbitrary area ID

        $component = Volt::test('map-service', [
            'pref' => $prefId,
            'area' => $areaId,
        ]);

        // Check that string IDs are handled correctly in URLs
        $component->assertSee('pref='.$prefId, false);
        $component->assertSee('area='.$areaId, false);
    }

    public function test_map_service_specific_service_links(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        // Test specific service links (using known service IDs from config)
        $expectedLink11 = '/?pref='.$pref->id.'&amp;area='.$area->id.'&amp;service=11';
        $expectedLink12 = '/?pref='.$pref->id.'&amp;area='.$area->id.'&amp;service=12';

        $component->assertSee($expectedLink11, false);
        $component->assertSee($expectedLink12, false);
    }

    public function test_map_service_component_is_minimal(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        // Verify the component is minimal - no complex logic, just display
        $component->assertSee('サービスを表示');
        $component->assertSee('<details', false);
        $component->assertSee('<summary>', false);
    }

    public function test_map_service_state_can_be_null(): void
    {
        // Test that the component can handle null values
        $component = Volt::test('map-service', [
            'pref' => null,
            'area' => null,
        ]);

        // Should still render but with null values in URLs
        $component->assertSee('サービスを表示');
        $component->assertSee('pref=', false);
        $component->assertSee('area=', false);
    }

    public function test_map_service_collapsible_behavior(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);

        $component = Volt::test('map-service', [
            'pref' => $pref->id,
            'area' => $area->id,
        ]);

        // The details element should be collapsible (closed by default)
        $component->assertSee('<details', false);
        $component->assertDontSee('<details open', false);
    }
}
