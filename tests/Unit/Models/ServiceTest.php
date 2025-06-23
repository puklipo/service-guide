<?php

namespace Tests\Unit\Models;

use App\Models\Facility;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_can_be_created(): void
    {
        $service = Service::factory()->create([
            'id' => 11,
            'name' => '居宅介護',
        ]);

        $this->assertDatabaseHas('services', [
            'id' => 11,
            'name' => '居宅介護',
        ]);

        $this->assertEquals(11, $service->id);
        $this->assertEquals('居宅介護', $service->name);
    }

    public function test_service_uses_non_incrementing_id(): void
    {
        $service = new Service;

        $this->assertFalse($service->incrementing);
    }

    public function test_service_has_many_facilities(): void
    {
        $service = Service::factory()->create();
        $facility1 = Facility::factory()->create(['service_id' => $service->id]);
        $facility2 = Facility::factory()->create(['service_id' => $service->id]);

        $this->assertCount(2, $service->facilities);
        $this->assertTrue($service->facilities->contains($facility1));
        $this->assertTrue($service->facilities->contains($facility2));
    }

    public function test_service_factory_creates_valid_service(): void
    {
        $service = Service::factory()->create();

        $this->assertNotNull($service->id);
        $this->assertNotNull($service->name);
        $this->assertIsString($service->name);
    }
}
