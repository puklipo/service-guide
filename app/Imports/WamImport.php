<?php

namespace App\Imports;

use App\Imports\Concerns\WithKana;
use App\Models\Area;
use App\Models\Company;
use App\Models\Facility;
use App\Models\Pref;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class WamImport implements OnEachRow, SkipsEmptyRows, SkipsOnFailure, WithChunkReading, WithHeadingRow, WithValidation
{
    use Importable;
    use SkipsFailures;
    use WithKana;

    public function __construct(private readonly int $service_id)
    {
        //
    }

    public function rules(): array
    {
        return [
            '事業所番号' => ['required', 'numeric', 'between:100000000,4800000000', Rule::notIn(config('deleted'))],
            '事業所の名称' => 'required',
            'NO（※システム内の固有の番号、連番）' => 'required',
            '都道府県コード又は市区町村コード' => ['required', 'size:5'],
            '法人番号' => ['required', 'digits:13', 'numeric', 'doesnt_start_with:0'],
        ];
    }

    public function onRow(Row $row): void
    {
        $pref = $this->pref($row);

        if (empty($pref) || $pref->doesntExist()) {
            return;
        }

        $area = $this->area($row, $pref);

        $company = $this->company($row);

        $data = [
            'no' => $this->kana($row['事業所番号']),
            'name' => $this->kana($row['事業所の名称']),
            'name_kana' => $this->kana($row['事業所の名称_かな']),
            'tel' => $this->kana($row['事業所電話番号']),
            'address' => $this->kana($row['事業所住所（番地以降）']),
            'url' => $row['事業所URL'] ?? '',
            'pref_id' => $pref->id,
            'area_id' => $area->id,
            'service_id' => $this->service_id,
        ];

        Facility::updateOrCreate([
            'wam' => $row['NO（※システム内の固有の番号、連番）'],
            'company_id' => $company->id,
        ], $data);
    }

    private function pref($row): ?Pref
    {
        $area_code = $row['都道府県コード又は市区町村コード'];

        // 都道府県コード(01-47)+3桁の市区町村コードの形式。最初の2文字から都道府県コードを得る。
        $pref_id = (int) Str::take(string: $area_code, limit: 2);

        return Pref::find($pref_id);
    }

    private function area($row, $pref): Area
    {
        $address = $row['事業所住所（市区町村）'];

        return $pref->areas()->updateOrCreate([
            'address' => $address,
        ], [
            'name' => $this->kana(Str::replaceFirst($pref->name, '', $address)),
        ]);
    }

    private function company($row): Company
    {
        return Company::updateOrCreate([
            'id' => (int) $row['法人番号'],
        ], [
            'name' => $this->kana($row['法人の名称']),
            'name_kana' => $row['法人の名称_かな'],
            'area' => $this->kana($row['法人住所（市区町村）']),
            'address' => $this->kana($row['法人住所（番地以降）']),
            'tel' => $row['法人電話番号'] ?? '',
            'url' => $row['法人URL'] ?? '',
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
