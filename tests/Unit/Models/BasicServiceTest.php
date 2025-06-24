<?php

namespace Tests\Unit\Models;

use App\Models\Service;
use Tests\TestCase;

class BasicServiceTest extends TestCase
{
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

    public function test_service_has_factory_trait(): void
    {
        $service = new Service;

        $this->assertTrue(method_exists($service, 'factory'));
    }
}
