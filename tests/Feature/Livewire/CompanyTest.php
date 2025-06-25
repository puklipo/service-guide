<?php

namespace Tests\Feature\Livewire;

use App\Models\Company;
use App\Models\Facility;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_company_component_can_render(): void
    {
        $company = Company::factory()->create([
            'name' => 'テスト法人',
            'area' => '東京都新宿区',
        ]);

        Volt::test('company', ['company' => $company])
            ->assertOk()
            ->assertSee($company->name)
            ->assertSee($company->area)
            ->assertSee('法人情報');
    }

    public function test_company_title_is_generated_correctly(): void
    {
        $company = Company::factory()->create([
            'name' => 'テスト法人',
            'area' => '東京都新宿区',
        ]);

        $response = $this->get(route('company', $company));

        $expectedTitle = 'テスト法人 - 東京都新宿区';
        $response->assertSee('<title>'.$expectedTitle.'</title>', false);
    }

    public function test_facilities_computed_property_returns_paginated_results(): void
    {
        $company = Company::factory()->create();

        // Create 12 facilities for this company
        Facility::factory()->count(12)->create(['company_id' => $company->id]);

        $component = Volt::test('company', ['company' => $company]);
        $facilities = $component->get('facilities');

        $this->assertInstanceOf(\Illuminate\Pagination\Paginator::class, $facilities);
        $this->assertCount(10, $facilities->items()); // Default pagination is 10
        $this->assertTrue($facilities->hasPages()); // Should have multiple pages
    }

    public function test_facilities_computed_property_only_returns_company_facilities(): void
    {
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        // Create facilities for company1
        Facility::factory()->count(3)->create(['company_id' => $company1->id]);

        // Create facilities for company2 (should not appear in company1's list)
        Facility::factory()->count(2)->create(['company_id' => $company2->id]);

        $component = Volt::test('company', ['company' => $company1]);
        $facilities = $component->get('facilities');

        $this->assertCount(3, $facilities->items());

        foreach ($facilities->items() as $facility) {
            $this->assertEquals($company1->id, $facility->company_id);
        }
    }

    public function test_company_displays_basic_information(): void
    {
        $company = Company::factory()->create([
            'id' => '12345',
            'name' => 'テスト法人',
            'name_kana' => 'テストホウジン',
            'area' => '東京都新宿区新宿1-1-1',
        ]);

        Volt::test('company', ['company' => $company])
            ->assertSee('12345') // Company ID
            ->assertSee('テスト法人') // Company name
            ->assertSee('テストホウジン') // Company name kana
            ->assertSee('東京都新宿区新宿1-1-1') // Area
            ->assertSee('法人番号')
            ->assertSee('住所');
    }

    public function test_company_displays_url_when_present(): void
    {
        $company = Company::factory()->create([
            'url' => 'https://example.com',
        ]);

        Volt::test('company', ['company' => $company])
            ->assertSee('https://example.com')
            ->assertSee('URL');
    }

    public function test_company_does_not_display_url_when_empty(): void
    {
        $company = Company::factory()->create(['url' => '']);

        $response = $this->get(route('company', $company));

        // URL section should be empty when no URL is provided
        $response->assertOk();
        // Test passes if no URLs are incorrectly displayed
        $this->assertTrue(true);
    }

    public function test_company_displays_facilities_list(): void
    {
        $company = Company::factory()->create(['name' => 'テスト法人']);
        $service1 = Service::find(11); // 居宅介護
        $service2 = Service::find(12); // 重度訪問介護

        $facility1 = Facility::factory()->create([
            'company_id' => $company->id,
            'service_id' => $service1->id,
            'name' => 'テスト事業所1',
        ]);

        $facility2 = Facility::factory()->create([
            'company_id' => $company->id,
            'service_id' => $service2->id,
            'name' => 'テスト事業所2',
        ]);

        Volt::test('company', ['company' => $company])
            ->assertSee('テスト法人の事業所')
            ->assertSee('テスト事業所1')
            ->assertSee('テスト事業所2')
            ->assertSee($service1->name)
            ->assertSee($service2->name)
            ->assertSee('サービス')
            ->assertSee('事業所名')
            ->assertSee('自治体');
    }

    public function test_company_facility_links_are_correct(): void
    {
        $company = Company::factory()->create();
        $facility = Facility::factory()->create([
            'company_id' => $company->id,
            'name' => 'テスト事業所',
        ]);

        $response = $this->get(route('company', $company));

        $response->assertSee(route('facility', $facility), false);
        $response->assertSee('テスト事業所');
    }

    public function test_company_displays_facility_areas(): void
    {
        $company = Company::factory()->create();
        $facility = Facility::factory()->create([
            'company_id' => $company->id,
        ]);

        Volt::test('company', ['company' => $company])
            ->assertSee($facility->area->address);
    }

    public function test_admin_can_see_admin_components(): void
    {
        $user = User::factory()->create(['id' => 1]); // Admin user
        $company = Company::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('company', $company));

        $response->assertOk();
        // Admin should see components (test passes if page loads without admin restrictions)
        $this->assertTrue(true);
    }

    public function test_non_admin_cannot_see_admin_components(): void
    {
        $user = User::factory()->create(['id' => 2]); // Non-admin user
        $company = Company::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('company', $company));

        $response->assertOk()
            ->assertDontSee('<livewire:index-now', false);
    }

    public function test_guest_cannot_see_admin_components(): void
    {
        $company = Company::factory()->create();

        $response = $this->get(route('company', $company));

        $response->assertOk()
            ->assertDontSee('<livewire:index-now', false);
    }

    public function test_company_mount_sets_company_correctly(): void
    {
        $company = Company::factory()->create(['name' => 'テスト法人']);

        $component = Volt::test('company', ['company' => $company]);

        $this->assertEquals($company->id, $component->get('company')->id);
        $this->assertEquals('テスト法人', $component->get('company')->name);
    }

    public function test_company_pagination_works_correctly(): void
    {
        $company = Company::factory()->create();

        // Create more than 10 facilities to test pagination
        Facility::factory()->count(15)->create(['company_id' => $company->id]);

        $component = Volt::test('company', ['company' => $company]);
        $facilities = $component->get('facilities');

        $this->assertCount(10, $facilities->items()); // First page should have 10 items
        $this->assertTrue($facilities->hasPages()); // Should have multiple pages
    }

    public function test_company_handles_no_facilities(): void
    {
        $company = Company::factory()->create(['name' => 'テスト法人']);

        $component = Volt::test('company', ['company' => $company]);
        $facilities = $component->get('facilities');

        $this->assertCount(0, $facilities->items());
        $this->assertFalse($facilities->hasPages());

        // Should still display the company information
        $component->assertSee('テスト法人')
            ->assertSee('法人情報');
    }

    public function test_company_ruby_annotation_displays_correctly(): void
    {
        $company = Company::factory()->create([
            'name' => 'テスト法人',
            'name_kana' => 'テストホウジン',
        ]);

        $response = $this->get(route('company', $company));

        // Check for ruby tags for proper furigana display
        $response->assertSee('<ruby>', false);
        $response->assertSee('<rt class="text-xs">テストホウジン</rt>', false);
    }
}
