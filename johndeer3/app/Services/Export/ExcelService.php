<?php

namespace App\Services\Export;

use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelService
{
    public function generateAndSave($data): string
    {
        try {
            $time = time();

            $name = "excel/reports_{$time}.xlsx";

            Excel::store(new ReportExport($data), $name,'public');

            return config('app.url') . '/storage/' . $name;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
