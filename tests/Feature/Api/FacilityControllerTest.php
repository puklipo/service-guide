<?php

namespace Tests\Feature\Api;

use App\Models\Area;
use App\Models\Company;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacilityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_api_returns_all_facilities_without_filters(): void
    {
        Facility::factory()->count(5)->create();

        $response = $this->getJson('/api/facilities');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'service',
                        'tel',
                        'url',
                        'company',
                    ],
                ],
                'links',
                'meta',
            ])
            ->assertJsonCount(5, 'data');
    }

    public function test_api_filters_facilities_by_service_name(): void
    {
        $service1 = Service::find(11); // 居宅介護
        $service2 = Service::find(12); // 重度訪問介護

        Facility::factory()->count(3)->create(['service_id' => $service1->id]);
        Facility::factory()->count(2)->create(['service_id' => $service2->id]);

        $response = $this->getJson('/api/facilities?service=居宅介護');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $response = $this->getJson('/api/facilities?service=重度訪問');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_api_filters_facilities_by_pref_name(): void
    {
        $pref1 = Pref::where('key', 'tokyo')->first(); // 東京都
        $pref2 = Pref::where('key', 'osaka')->first(); // 大阪府

        Facility::factory()->count(3)->create(['pref_id' => $pref1->id]);
        Facility::factory()->count(2)->create(['pref_id' => $pref2->id]);

        $response = $this->getJson('/api/facilities?pref=東京');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $response = $this->getJson('/api/facilities?pref=大阪');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_api_filters_facilities_by_area_name(): void
    {
        $area1 = Area::factory()->create(['name' => '渋谷区']);
        $area2 = Area::factory()->create(['name' => '新宿区']);

        Facility::factory()->count(3)->create(['area_id' => $area1->id]);
        Facility::factory()->count(2)->create(['area_id' => $area2->id]);

        $response = $this->getJson('/api/facilities?area=渋谷');

        $response->assertOk()
            ->assertJsonCount(3, 'data');

        $response = $this->getJson('/api/facilities?area=新宿');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_api_combines_multiple_filters(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['name' => '渋谷区', 'pref_id' => $pref->id]);
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

        $response = $this->getJson('/api/facilities?pref=東京&area=渋谷&service=居宅介護');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_api_returns_correct_facility_resource_structure(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create([
            'name' => '渋谷区',
            'address' => '東京都渋谷区',
            'pref_id' => $pref->id,
        ]);
        $company = Company::factory()->create(['name' => '株式会社テスト']);
        $service = Service::find(11); // 居宅介護

        $facility = Facility::factory()->create([
            'name' => 'テスト施設',
            'address' => '1-1-1',
            'tel' => '03-1234-5678',
            'pref_id' => $pref->id,
            'area_id' => $area->id,
            'company_id' => $company->id,
            'service_id' => $service->id,
        ]);

        $response = $this->getJson('/api/facilities');

        $response->assertOk()
            ->assertJsonFragment([
                'name' => 'テスト施設',
                'address' => '東京都渋谷区1-1-1',
                'service' => '居宅介護',
                'tel' => '03-1234-5678',
                'company' => '株式会社テスト',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'service',
                        'tel',
                        'url',
                        'company',
                    ],
                ],
            ]);

        $responseData = $response->json('data.0');
        $this->assertStringContainsString('/facilities/', $responseData['url']);
        $this->assertEquals($facility->id, $responseData['id']);
    }

    public function test_api_returns_empty_results_for_non_matching_filters(): void
    {
        Facility::factory()->count(5)->create();

        $response = $this->getJson('/api/facilities?service=存在しないサービス');

        $response->assertOk()
            ->assertJsonCount(0, 'data');

        $response = $this->getJson('/api/facilities?pref=存在しない県');

        $response->assertOk()
            ->assertJsonCount(0, 'data');

        $response = $this->getJson('/api/facilities?area=存在しない地域');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_api_handles_partial_matches_with_like_operator(): void
    {
        $service = Service::find(11); // 居宅介護 (will match partial '居宅介護')
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $area = Area::factory()->create(['name' => '渋谷区']);

        Facility::factory()->create(['service_id' => $service->id]);
        Facility::factory()->create(['pref_id' => $pref->id]);
        Facility::factory()->create(['area_id' => $area->id]);

        // Test partial service name match
        $response = $this->getJson('/api/facilities?service=居宅介護');
        $response->assertOk()->assertJsonCount(1, 'data');

        // Test partial pref name match
        $response = $this->getJson('/api/facilities?pref=東京');
        $response->assertOk()->assertJsonCount(1, 'data');

        // Test partial area name match
        $response = $this->getJson('/api/facilities?area=渋谷');
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_api_includes_pagination_metadata(): void
    {
        Facility::factory()->count(20)->create();

        $response = $this->getJson('/api/facilities');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'path',
                    'per_page',
                    'to',
                ],
            ]);
    }

    public function test_api_maintains_query_string_in_pagination(): void
    {
        $service = Service::find(11); // 居宅介護
        Facility::factory()->count(20)->create(['service_id' => $service->id]);

        $response = $this->getJson('/api/facilities?service=居宅介護');

        $response->assertOk();

        $links = $response->json('links');
        if (isset($links['next'])) {
            $this->assertStringContainsString('service=居宅介護', $links['next']);
        }
    }
}
