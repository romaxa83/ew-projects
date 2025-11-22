<?php

namespace App\Imports;

use App\Dto\Branches\BranchDto;
use App\Exceptions\Branches\SimilarBranchException;
use App\Models\Locations\RegionTranslate;
use App\Services\Branches\BranchService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BranchesImport implements ToCollection, WithValidation, WithHeadingRow
{
    use Importable;

    public function __construct(private BranchService $service)
    { }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $branch) {
            $dto = BranchDto::byArgs(
                [
                    'name' => $branch['name'],
                    'city' => $branch['city'],
                    'region_id' => RegionTranslate::whereTitle($branch['region'])
                        ->first()
                        ->row_id,
                    'address' => $branch['address'],
                    'active' => true,
                    'phones' => array_merge(
                        [
                            [
                                'phone' => $branch['phone1'],
                                'is_default' => true,
                            ]
                        ],
                        !empty($branch['phone2']) ? [
                            [
                                'phone' => $branch['phone2'],
                                'is_default' => false,
                            ]
                        ] : [],
                        !empty($branch['phone3']) ? [
                            [
                                'phone' => $branch['phone3'],
                                'is_default' => false,
                            ]
                        ] : []
                    )
                ]
            );

            try {
                $this->service->create($dto);
            } catch (SimilarBranchException) {
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string'
            ],
            'city' => [
                'required',
                'string',
            ],
            'region' => [
                'required',
                'string',
                Rule::exists(RegionTranslate::class, 'title')
            ],
            'address' => [
                'required',
                'string',
            ],
            'phone1' => [
                'required',
                'regex:/^380[1-9][0-9]{8}/',
            ],
            'phone2' => [
                'nullable',
                'regex:/^380[1-9][0-9]{8}/',
            ],
            'phone3' => [
                'nullable',
                'regex:/^380[1-9][0-9]{8}/',
            ],
        ];
    }
}
