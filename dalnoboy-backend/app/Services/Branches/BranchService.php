<?php


namespace App\Services\Branches;


use App\Contracts\Models\HasGuard;
use App\Dto\Branches\BranchDto;
use App\Dto\PhoneDto;
use App\Exceptions\Branches\BranchHasEmployeesException;
use App\Exceptions\Branches\SimilarBranchException;
use App\Exceptions\Utilities\ImportFileIncorrectException;
use App\Exceptions\Utilities\NothingToExportException;
use App\Exports\BranchesExport;
use App\Exports\ImportExample;
use App\Imports\BranchesImport;
use App\Models\Branches\Branch;
use App\Traits\HasDownload;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class BranchService
{
    use HasDownload;

    public function create(BranchDto $dto): Branch
    {
        return $this->editBranch($dto, new Branch());
    }

    private function editBranch(BranchDto $dto, Branch $branch): Branch
    {
        $simpleBranch = Branch::filter(
            [
                'address' => $dto->getAddress(),
                'region_id' => $dto->getRegionId(),
                'city' => $dto->getCity()
            ]
        )
            ->where(Branch::TABLE . '.id', '<>', $branch->id)
            ->first();

        if ($simpleBranch) {
            throw new SimilarBranchException($simpleBranch->name);
        }

        if ($branch->id && !$dto->isActive() && $branch->users()
                ->exists()) {
            throw new BranchHasEmployeesException();
        }

        $branch->name = $dto->getName();
        $branch->address = $dto->getAddress();
        $branch->region_id = $dto->getRegionId();
        $branch->city = $dto->getCity();
        $branch->active = $dto->isActive();
        $branch->save();

        $this->setPhones($dto->getPhones(), $branch);

        return $branch->refresh();
    }

    /**
     * @param PhoneDto[] $phones
     * @param Branch $branch
     */
    private function setPhones(array $phones, Branch $branch): void
    {
        $branch
            ->phones()
            ->delete();

        $branch->phones()
            ->createMany(
                array_map(
                    fn(PhoneDto $phoneDto) => [
                        'phone' => $phoneDto->getPhone(),
                        'is_default' => $phoneDto->isDefault()
                    ],
                    $phones
                )
            );
    }

    public function update(BranchDto $dto, Branch $branch): Branch
    {
        return $this->editBranch($dto, $branch);
    }

    public function toggleActive(Branch $branch): Branch
    {
        if ($branch->active && $branch->users()
                ->exists()) {
            throw new BranchHasEmployeesException();
        }
        $branch->active = !$branch->active;
        $branch->save();

        return $branch;
    }

    public function delete(Branch $branch): bool
    {
        if ($branch->users()
            ->exists()) {
            throw new BranchHasEmployeesException();
        }
        return $branch->delete();
    }

    public function show(array $args): LengthAwarePaginator
    {
        return Branch::filter($args)
            ->withCount('inspections')
            ->orderBy('created_at')
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }

    public function list(array $args, HasGuard $user): Collection
    {
        return Branch::filter($args)
            ->activeGuard($user)
            ->get();
    }

    public function import(UploadedFile $file): bool
    {
        try {
            Excel::import(new BranchesImport($this), $file);
        } catch (ValidationException) {
            throw new ImportFileIncorrectException();
        }
        return true;
    }

    public function export(): array
    {
        $branches = Branch::with(['region.translate', 'phones'])
            ->withCount('inspections')
            ->get();

        if ($branches->isEmpty()) {
            throw new NothingToExportException();
        }

        return [
            'link' => $this->getDownloadXlsxLink(
                $branches->toArray(),
                'branches',
                BranchesExport::class
            )
        ];
    }

    public function importExample(): array
    {
        return [
            'link' => $this->getDownloadXlsxLink(
                [
                    [
                        'name',
                        'city',
                        'region',
                        'address',
                        'phone1',
                        'phone2',
                        'phone3',
                    ]
                ],
                'import_example',
                ImportExample::class
            )
        ];
    }
}
