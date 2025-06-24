<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Facility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_company_can_be_created(): void
    {
        $company = Company::factory()->create([
            'id' => 1234567890,
            'name' => '株式会社テスト',
            'name_kana' => 'カブシキガイシャテスト',
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => 1234567890,
            'name' => '株式会社テスト',
            'name_kana' => 'カブシキガイシャテスト',
        ]);

        $this->assertEquals(1234567890, $company->id);
        $this->assertEquals('株式会社テスト', $company->name);
    }

    public function test_company_uses_non_incrementing_id(): void
    {
        $company = new Company;

        $this->assertFalse($company->incrementing);
    }

    public function test_company_has_many_facilities(): void
    {
        $company = Company::factory()->create();
        $facility1 = Facility::factory()->create(['company_id' => $company->id]);
        $facility2 = Facility::factory()->create(['company_id' => $company->id]);

        $this->assertCount(2, $company->facilities);
        $this->assertTrue($company->facilities->contains($facility1));
        $this->assertTrue($company->facilities->contains($facility2));
    }

    public function test_company_fillable_attributes(): void
    {
        $company = new Company;

        $expectedFillable = [
            'id',
            'name',
            'name_kana',
            'area',
            'address',
            'tel',
            'url',
        ];

        $this->assertEquals($expectedFillable, $company->getFillable());
    }

    public function test_company_telephone_cast(): void
    {
        $company = Company::factory()->create([
            'tel' => '03-1234-5678',
        ]);

        $casts = $company->getCasts();
        $this->assertArrayHasKey('tel', $casts);
        $this->assertEquals('App\Casts\Telephone', $casts['tel']);
    }

    public function test_company_factory_creates_valid_company(): void
    {
        $company = Company::factory()->create();

        $this->assertNotNull($company->id);
        $this->assertNotNull($company->name);
        $this->assertNotNull($company->address);
        $this->assertIsInt($company->id); // Company ID is unsignedBigInteger, so should be integer
        $this->assertIsString($company->name);
        $this->assertIsString($company->address);
    }
}
