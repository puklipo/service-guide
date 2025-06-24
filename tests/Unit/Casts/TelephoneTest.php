<?php

namespace Tests\Unit\Casts;

use App\Casts\Telephone;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelephoneTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_telephone_cast_returns_original_value_when_no_patch_exists(): void
    {
        $company = Company::factory()->create(['id' => 'test123']);

        config(['patch' => []]);

        $cast = new Telephone;
        $result = $cast->get($company, 'tel', '03-1234-5678', []);

        $this->assertEquals('03-1234-5678', $result);
    }

    public function test_telephone_cast_returns_patched_value_when_patch_exists(): void
    {
        $company = Company::factory()->create(['id' => 'test123']);

        config(['patch' => [
            'test123' => [
                'tel' => '03-9999-0000',
            ],
        ]]);

        $cast = new Telephone;
        $result = $cast->get($company, 'tel', '03-1234-5678', []);

        $this->assertEquals('03-9999-0000', $result);
    }

    public function test_telephone_cast_set_returns_value_unchanged(): void
    {
        $company = Company::factory()->create();
        $cast = new Telephone;

        $result = $cast->set($company, 'tel', '03-1234-5678', []);

        $this->assertEquals('03-1234-5678', $result);
    }

    public function test_telephone_cast_handles_null_values(): void
    {
        $company = Company::factory()->create(['id' => 'test123']);

        config(['patch' => []]);

        $cast = new Telephone;
        $result = $cast->get($company, 'tel', null, []);

        $this->assertNull($result);
    }

    public function test_telephone_cast_integration_with_company_model(): void
    {        
        $company = Company::factory()->create(['id' => 'company123']);
        
        config(['patch' => [
            'company123' => [
                'tel' => '03-patched-number',
            ],
        ]]);

        // Update the company with tel value using raw database update to avoid cast during creation
        $company->update(['tel' => '03-original-number']);

        // Test that the cast is applied when accessing the attribute
        $this->assertEquals('03-patched-number', $company->fresh()->tel);
    }
}
