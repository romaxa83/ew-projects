<?php

namespace WezomCms\Providers\Exports;

use Exception;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use WezomCms\Providers\Repositories\ProviderRepository;

class ProviderExport implements FromCollection,
    WithMapping,
    WithHeadings
{
    public function __construct(
        protected Request $request,
        protected ProviderRepository $repo
    )
    {}

    /**
     * @throws Exception
     */
    public function collection()
    {
        return $this->repo->getAll([], true, [], false);
    }

    public function map($model): array
    {
        return [
            $model->id,
            $model->name,
            $model->phone,
            $model->email,
            $model->company,
            $model->created_at->format(config('cms.core.time.format.created_at.import'))
        ];
    }

    public function headings(): array
    {
        return [
            __('cms-core::admin.layout.ID'),
            __('cms-providers::admin.provider.Name'),
            __('cms-providers::admin.Phone'),
            __('cms-providers::admin.Email'),
            __('cms-providers::admin.company.Name'),
            __('cms-core::admin.layout.Created at'),
        ];
    }
}

