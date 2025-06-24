<?php

namespace Tests\Unit\Models;

use App\Models\Area;
use App\Models\Company;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use App\Support\IndexNow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FacilityTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_facility_can_be_created(): void
    {
        $pref = Pref::where('key', 'tokyo')->first();
        $area = Area::factory()->create(['pref_id' => $pref->id]);
        $company = Company::factory()->create();
        $service = Service::find(11);

        $facility = Facility::factory()->create([
            'wam' => '1234567890',
            'name' => 'テスト施設',
            'pref_id' => $pref->id,
            'area_id' => $area->id,
            'company_id' => $company->id,
            'service_id' => $service->id,
        ]);

        $this->assertDatabaseHas('facilities', [
            'wam' => '1234567890',
            'name' => 'テスト施設',
            'pref_id' => $pref->id,
            'area_id' => $area->id,
            'company_id' => $company->id,
            'service_id' => $service->id,
        ]);

        $this->assertEquals('1234567890', $facility->wam);
        $this->assertEquals('テスト施設', $facility->name);
    }

    public function test_facility_uses_ulids(): void
    {
        $facility = Facility::factory()->create();

        $this->assertNotNull($facility->id);
        $this->assertIsString($facility->id);
        $this->assertEquals(26, strlen($facility->id));
    }

    public function test_facility_belongs_to_pref(): void
    {
        $pref = Pref::where('key', 'tokyo')->first(); // 東京都
        $facility = Facility::factory()->create(['pref_id' => $pref->id]);

        $this->assertEquals($pref->id, $facility->pref->id);
        $this->assertEquals($pref->name, $facility->pref->name);
    }

    public function test_facility_belongs_to_area(): void
    {
        $area = Area::factory()->create(['name' => '渋谷区']);
        $facility = Facility::factory()->create(['area_id' => $area->id]);

        $this->assertEquals($area->id, $facility->area->id);
        $this->assertEquals('渋谷区', $facility->area->name);
    }

    public function test_facility_belongs_to_company(): void
    {
        $company = Company::factory()->create(['name' => '株式会社テスト']);
        $facility = Facility::factory()->create(['company_id' => $company->id]);

        $this->assertEquals($company->id, $facility->company->id);
        $this->assertEquals('株式会社テスト', $facility->company->name);
    }

    public function test_facility_belongs_to_service(): void
    {
        $service = Service::find(11); // 居宅介護
        $facility = Facility::factory()->create(['service_id' => $service->id]);

        $this->assertEquals($service->id, $facility->service->id);
        $this->assertEquals($service->name, $facility->service->name);
    }

    public function test_facility_fillable_attributes(): void
    {
        $facility = new Facility;

        $expectedFillable = [
            'wam',
            'name',
            'name_kana',
            'address',
            'tel',
            'url',
            'no',
            'pref_id',
            'area_id',
            'company_id',
            'service_id',
        ];

        $this->assertEquals($expectedFillable, $facility->getFillable());
    }

    public function test_facility_eager_loads_relationships(): void
    {
        $facility = new Facility;

        $expectedWith = ['service', 'area', 'company'];
        $this->assertEquals($expectedWith, $facility->with);
    }

    public function test_facility_queues_index_now_on_creation_in_production(): void
    {
        $this->app['env'] = 'production';
        Queue::fake();

        Facility::factory()->create();

        Queue::assertPushed(function (object $job) {
            return str_contains(get_class($job), 'CallQueuedClosure');
        });
    }

    public function test_facility_does_not_queue_index_now_in_non_production(): void
    {
        $this->app['env'] = 'testing';
        Queue::fake();

        Facility::factory()->create();

        Queue::assertNothingPushed();
    }

    public function test_facility_factory_creates_valid_facility(): void
    {
        $facility = Facility::factory()->create();

        $this->assertNotNull($facility->id);
        $this->assertNotNull($facility->wam);
        $this->assertNotNull($facility->name);
        $this->assertNotNull($facility->address);
        $this->assertNotNull($facility->pref_id);
        $this->assertNotNull($facility->area_id);
        $this->assertNotNull($facility->company_id);
        $this->assertNotNull($facility->service_id);

        $this->assertIsString($facility->name);
        $this->assertIsString($facility->address);
    }
}
