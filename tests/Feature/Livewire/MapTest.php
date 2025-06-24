<?php

namespace Tests\Feature\Livewire;

use App\Models\Area;
use App\Models\Pref;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class MapTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_map_component_can_render(): void
    {
        Volt::test('map')
            ->assertOk()
            ->assertSee('サイトマップ')
            ->assertSee('自治体一覧');
    }

    public function test_map_title_is_generated_correctly(): void
    {
        $response = $this->get('/map');
        
        $expectedTitle = 'サイトマップ ' . config('app.name');
        $response->assertSee('<title>' . $expectedTitle . '</title>', false);
    }

    public function test_prefs_computed_property_returns_all_prefectures_with_areas(): void
    {
        // Create some test areas for existing prefectures
        $tokyo = Pref::where('key', 'tokyo')->first();
        $osaka = Pref::where('key', 'osaka')->first();
        
        Area::factory()->count(3)->create(['pref_id' => $tokyo->id]);
        Area::factory()->count(2)->create(['pref_id' => $osaka->id]);

        $component = Volt::test('map');
        $prefs = $component->get('prefs');

        $this->assertGreaterThan(0, $prefs->count());
        
        // Check that areas are properly loaded
        $tokyoPref = $prefs->firstWhere('id', $tokyo->id);
        $osakaPref = $prefs->firstWhere('id', $osaka->id);
        
        $this->assertNotNull($tokyoPref);
        $this->assertNotNull($osakaPref);
        $this->assertTrue($tokyoPref->relationLoaded('areas'));
        $this->assertTrue($osakaPref->relationLoaded('areas'));
    }

    public function test_map_displays_prefecture_names(): void
    {
        $tokyo = Pref::where('key', 'tokyo')->first();
        $osaka = Pref::where('key', 'osaka')->first();

        Volt::test('map')
            ->assertSee($tokyo->name)  // 東京都
            ->assertSee($osaka->name); // 大阪府
    }

    public function test_map_displays_prefecture_links(): void
    {
        $tokyo = Pref::where('key', 'tokyo')->first();
        
        // Check by visiting the route that uses this component
        $response = $this->get('/map');
        
        // Check for prefecture filter link
        $expectedLink = '/?pref=' . $tokyo->id;
        $response->assertSee($expectedLink, false);
    }

    public function test_map_displays_area_names_and_links(): void
    {
        $tokyo = Pref::where('key', 'tokyo')->first();
        $area1 = Area::factory()->create([
            'pref_id' => $tokyo->id,
            'name' => '渋谷区',
        ]);
        $area2 = Area::factory()->create([
            'pref_id' => $tokyo->id,
            'name' => '新宿区',
        ]);

        $response = $this->get('/map');
        
        $response->assertSee('渋谷区')
            ->assertSee('新宿区');
        
        // Check for area filter links
        $expectedLink1 = '/?pref=' . $tokyo->id . '&amp;area=' . $area1->id;
        $expectedLink2 = '/?pref=' . $tokyo->id . '&amp;area=' . $area2->id;
        
        $response->assertSee($expectedLink1, false);
        $response->assertSee($expectedLink2, false);
    }

    public function test_map_uses_prefecture_keys_for_anchors(): void
    {
        $tokyo = Pref::where('key', 'tokyo')->first();
        $osaka = Pref::where('key', 'osaka')->first();

        $response = $this->get('/map');
        
        // Check for anchor IDs using prefecture keys
        $response->assertSee('id="' . $tokyo->key . '"', false);
        $response->assertSee('id="' . $osaka->key . '"', false);
        
        // Check for scrollspy navigation links
        $response->assertSee('href="#' . $tokyo->key . '"', false);
        $response->assertSee('href="#' . $osaka->key . '"', false);
    }

    public function test_map_scrollspy_navigation_structure(): void
    {
        $response = $this->get('/map');
        
        // Check for scrollspy structure
        $response->assertSee('data-scrollspy="#scrollspy"', false);
        $response->assertSee('data-scrollspy-scrollable-parent="#scrollspy-scrollable-parent"', false);
        $response->assertSee('id="scrollspy"', false);
        $response->assertSee('id="scrollspy-scrollable-parent"', false);
    }

    public function test_map_grid_layout_structure(): void
    {
        $response = $this->get('/map');
        
        // Check for grid layout classes
        $response->assertSee('grid grid-cols-5', false);
        $response->assertSee('col-span-2 sm:col-span-1', false);
        $response->assertSee('col-span-3 sm:col-span-4', false);
    }

    public function test_map_displays_instruction_text(): void
    {
        Volt::test('map')
            ->assertSee('自治体一覧。ページ内を検索してください。');
    }

    public function test_map_handles_prefectures_without_areas(): void
    {
        $prefWithoutAreas = Pref::where('key', 'tokyo')->first();
        
        // Ensure this prefecture has no areas for this test
        $prefWithoutAreas->areas()->delete();

        $response = $this->get('/map');
        
        // Should still display the prefecture name and not cause any errors
        $response->assertOk();
        $response->assertSee($prefWithoutAreas->name);
    }

    public function test_map_prefecture_wire_keys_are_unique(): void
    {
        $tokyo = Pref::where('key', 'tokyo')->first();
        $osaka = Pref::where('key', 'osaka')->first();

        $response = $this->get('/map');
        
        // Check that wire:key attributes use prefecture IDs
        $response->assertSee('wire:key="' . $tokyo->id . '"', false);
        $response->assertSee('wire:key="' . $osaka->id . '"', false);
    }

    public function test_map_area_wire_keys_are_unique(): void
    {
        $tokyo = Pref::where('key', 'tokyo')->first();
        $area1 = Area::factory()->create(['pref_id' => $tokyo->id]);
        $area2 = Area::factory()->create(['pref_id' => $tokyo->id]);

        $response = $this->get('/map');
        
        // Check that wire:key attributes use area IDs
        $response->assertSee('wire:key="' . $area1->id . '"', false);
        $response->assertSee('wire:key="' . $area2->id . '"', false);
    }

    public function test_map_uses_correct_css_classes(): void
    {
        $response = $this->get('/map');
        
        // Check for specific CSS classes used in the component
        $response->assertSee('bg-primary text-primary-content', false);
        $response->assertSee('link link-primary link-animated', false);
        $response->assertSee('scrollspy-active:text-primary', false);
        $response->assertSee('list-disc list-inside', false);
    }

    public function test_map_accessibility_structure(): void
    {
        $response = $this->get('/map');
        
        // Check for proper heading structure
        $response->assertSee('<h2 class="text-4xl my-6">サイトマップ</h2>', false);
        
        // Check for proper list structure
        $response->assertSee('<ul class="ml-6 list-disc list-inside">', false);
    }
}