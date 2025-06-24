<?php

namespace Tests\Unit\Models;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_uses_non_incrementing_id(): void
    {
        $service = new Service;

        $this->assertFalse($service->incrementing);
    }

    public function test_service_can_be_instantiated(): void
    {
        $service = new Service;

        $this->assertInstanceOf(Service::class, $service);
    }
}
