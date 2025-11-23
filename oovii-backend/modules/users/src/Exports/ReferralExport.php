<?php

namespace WezomCms\Users\Exports;

use Exception;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use WezomCms\Users\Repositories\UserRepository;

class ReferralExport implements FromCollection,
    WithMapping,
    WithHeadings
{
    public function __construct(
        protected Request $request,
        protected UserRepository $repo
    )
    {}

    /**
     * @throws Exception
     */
    public function collection()
    {
        return $this->repo->getInviters();
    }

    public function map($model): array
    {
        return [
            $model->id,
            $model->full_name,
            $model->email,
            $model->referrals_count,
            $model->bonus,
        ];
    }

    public function headings(): array
    {
        return [
            __('cms-core::admin.layout.ID'),
            __('cms-users::admin.Full name'),
            __('cms-users::admin.E-mail'),
            __('cms-users::admin.Referrals number'),
            __('cms-users::admin.referrals.Bonus sum'),
        ];
    }
}

