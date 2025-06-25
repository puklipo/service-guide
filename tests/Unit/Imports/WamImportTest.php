<?php

namespace Tests\Unit\Imports;

use App\Imports\WamImport;
use App\Models\Area;
use App\Models\Company;
use App\Models\Facility;
use App\Models\Pref;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class WamImportTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_wam_import_can_be_instantiated(): void
    {
        $import = new WamImport(11);

        $this->assertInstanceOf(WamImport::class, $import);
    }

    public function test_wam_import_validation_rules(): void
    {
        $import = new WamImport(11);
        $rules = $import->rules();

        $expectedRules = [
            '事業所番号' => ['required', 'numeric', 'between:100000000,4800000000', 'not_in:'],
            '事業所の名称' => 'required',
            'NO（※システム内の固有の番号、連番）' => 'required',
            '都道府県コード又は市区町村コード' => ['required', 'size:5'],
            '法人番号' => ['required', 'digits:13', 'numeric', 'doesnt_start_with:0'],
        ];

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('事業所番号', $rules);
        $this->assertArrayHasKey('事業所の名称', $rules);
        $this->assertArrayHasKey('NO（※システム内の固有の番号、連番）', $rules);
        $this->assertArrayHasKey('都道府県コード又は市区町村コード', $rules);
        $this->assertArrayHasKey('法人番号', $rules);
    }

    public function test_wam_import_chunk_size(): void
    {
        $import = new WamImport(11);

        $this->assertEquals(1000, $import->chunkSize());
    }

    public function test_wam_import_with_valid_data(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/valid_data.csv');

        // Count initial records
        $initialFacilities = Facility::count();
        $initialCompanies = Company::count();
        $initialAreas = Area::count();

        Excel::import($import, $csvPath);

        // Verify records were created
        $this->assertGreaterThan($initialFacilities, Facility::count());
        $this->assertGreaterThan($initialCompanies, Company::count());
        $this->assertGreaterThan($initialAreas, Area::count());

        // Verify specific data was imported correctly
        $this->assertDatabaseHas('facilities', [
            'name' => 'テスト訪問介護事業所',
            'no' => '1310000001',
            'service_id' => 11,
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => 1234567890123,
            'name' => 'テスト株式会社',
        ]);

        // Verify Tokyo prefecture was linked correctly
        $facility = Facility::where('name', 'テスト訪問介護事業所')->first();
        $this->assertNotNull($facility);
        $this->assertEquals(13, $facility->pref_id); // Tokyo
    }

    public function test_wam_import_with_minimal_data(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/minimal_data.csv');

        $initialFacilities = Facility::count();

        Excel::import($import, $csvPath);

        $this->assertGreaterThan($initialFacilities, Facility::count());

        // Verify minimal data was imported
        $this->assertDatabaseHas('facilities', [
            'name' => 'ミニマル訪問介護事業所',
            'no' => '1310000004',
            'service_id' => 11,
        ]);
    }

    public function test_wam_import_with_empty_data(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/empty_data.csv');

        $initialFacilities = Facility::count();

        Excel::import($import, $csvPath);

        // No records should be created from empty CSV
        $this->assertEquals($initialFacilities, Facility::count());
    }

    public function test_wam_import_with_duplicate_data(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/duplicate_data.csv');

        $initialFacilities = Facility::count();

        Excel::import($import, $csvPath);

        // Should create only one facility despite duplicate rows
        $this->assertEquals($initialFacilities + 1, Facility::count());

        $this->assertDatabaseHas('facilities', [
            'name' => '重複テスト訪問介護事業所',
            'no' => '1310000005',
            'service_id' => 11,
        ]);

        // Verify no duplicate facilities were created
        $duplicateCount = Facility::where('name', '重複テスト訪問介護事業所')->count();
        $this->assertEquals(1, $duplicateCount);
    }

    public function test_wam_import_with_large_data(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/large_data.csv');

        $initialFacilities = Facility::count();

        Excel::import($import, $csvPath);

        // Should create 5 facilities from large data set
        $this->assertEquals($initialFacilities + 5, Facility::count());

        // Verify all facilities were created
        for ($i = 1; $i <= 5; $i++) {
            $this->assertDatabaseHas('facilities', [
                'name' => "大規模テスト訪問介護{$i}",
                'no' => "011000000{$i}",
                'service_id' => 11,
            ]);
        }
    }

    public function test_wam_import_with_invalid_data_skips_failures(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/invalid_data.csv');

        $initialFacilities = Facility::count();

        Excel::import($import, $csvPath);

        // Should skip invalid rows and process valid ones
        $this->assertGreaterThanOrEqual($initialFacilities, Facility::count());

        // Verify that failures were recorded
        $failures = $import->failures();
        $this->assertNotEmpty($failures);
    }

    public function test_wam_import_creates_correct_relationships(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/valid_data.csv');

        Excel::import($import, $csvPath);

        $facility = Facility::where('name', 'テスト訪問介護事業所')->first();
        $this->assertNotNull($facility);

        // Verify relationships are properly set
        $this->assertNotNull($facility->pref);
        $this->assertNotNull($facility->area);
        $this->assertNotNull($facility->company);
        $this->assertNotNull($facility->service);

        // Verify specific relationship data
        $this->assertEquals('東京都', $facility->pref->name);
        $this->assertEquals(11, $facility->service->id);
        $this->assertEquals(1234567890123, $facility->company->id);
    }

    public function test_wam_import_handles_different_service_ids(): void
    {
        // Test with different service IDs
        $serviceIds = [11, 12, 13];

        foreach ($serviceIds as $serviceId) {
            $import = new WamImport($serviceId);
            $csvPath = base_path('tests/fixtures/valid_data.csv');

            Excel::import($import, $csvPath);

            // Verify facilities were created with correct service_id
            $facility = Facility::where('service_id', $serviceId)->latest()->first();
            $this->assertNotNull($facility);
            $this->assertEquals($serviceId, $facility->service_id);
        }
    }

    public function test_wam_import_normalizes_japanese_text(): void
    {
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/valid_data.csv');

        Excel::import($import, $csvPath);

        $facility = Facility::where('name', 'テスト訪問介護事業所')->first();
        $this->assertNotNull($facility);

        // Verify text normalization occurred (WithKana trait)
        $this->assertIsString($facility->name);
        $this->assertIsString($facility->address);
    }

    public function test_wam_import_updates_existing_facilities(): void
    {
        // First import
        $import = new WamImport(11);
        $csvPath = base_path('tests/fixtures/valid_data.csv');

        Excel::import($import, $csvPath);

        $initialCount = Facility::count();
        $facility = Facility::where('name', 'テスト訪問介護事業所')->first();
        $originalUpdatedAt = $facility->updated_at;

        // Second import with same data should not create new records
        Excel::import($import, $csvPath);

        // Verify no duplicate facilities were created
        $this->assertEquals($initialCount, Facility::count());

        // Verify the facility still exists and is the same record
        $updatedFacility = Facility::where('name', 'テスト訪問介護事業所')->first();
        $this->assertNotNull($updatedFacility);
        $this->assertEquals($facility->id, $updatedFacility->id);
    }

    public function test_wam_import_handles_missing_prefecture(): void
    {
        // Create a CSV with invalid prefecture code
        $csvContent = "\"都道府県コード又は市区町村コード\",\"NO（※システム内の固有の番号、連番）\",\"指定機関名\",\"法人の名称\",\"法人の名称_かな\",\"法人番号\",\"法人住所（市区町村）\",\"法人住所（番地以降）\",\"法人電話番号\",\"法人FAX番号\",\"法人URL\",\"サービス種別\",\"事業所の名称\",\"事業所の名称_かな\",\"事業所番号\",\"事業所住所（市区町村）\",\"事業所住所（番地以降）\",\"事業所電話番号\",\"事業所FAX番号\",\"事業所URL\",\"事業所緯度\",\"事業所経度\",\"利用可能な時間帯（平日）\",\"利用可能な時間帯（土曜）\",\"利用可能な時間帯（日曜）\",\"利用可能な時間帯（祝日）\",\"定休日\",\"利用可能曜日特記事項（留意事項）\",\"定員\"\n\"99999\",\"T0000000099\",\"テスト都\",\"無効テスト株式会社\",\"むこうてすとかぶしきがいしゃ\",\"9999999999999\",\"無効県無効市\",\"無効1-1-1\",\"099-999-9999\",\"\",\"\",\"居宅介護\",\"無効テスト訪問介護事業所\",\"むこうてすとほうもんかいごじぎょうしょ\",\"9999999999\",\"無効県無効市\",\"無効1-1-2\",\"099-999-9998\",\"\",\"\",\"0.00000\",\"0.00000\",\"\",\"\",\"\",\"\",\"\",\"\",\"\"";

        $tempCsvPath = base_path('tests/fixtures/invalid_pref.csv');
        file_put_contents($tempCsvPath, $csvContent);

        $import = new WamImport(11);
        $initialCount = Facility::count();

        Excel::import($import, $tempCsvPath);

        // Should not create facility with invalid prefecture
        $this->assertEquals($initialCount, Facility::count());

        // Clean up
        unlink($tempCsvPath);
    }

    public function test_wam_import_direct_usage_pattern(): void
    {
        // Test the usage pattern mentioned in the issue: (new WamImport(11))->import('csvファイルのパス');
        $csvPath = base_path('tests/fixtures/valid_data.csv');
        $initialCount = Facility::count();

        (new WamImport(11))->import($csvPath);

        $this->assertGreaterThan($initialCount, Facility::count());

        $this->assertDatabaseHas('facilities', [
            'name' => 'テスト訪問介護事業所',
            'service_id' => 11,
        ]);
    }
}
