<?php

namespace App\Http\Controllers\Settings\Rate;

use App\DataTables\Settings\Rate\Employee\RateDataTable;
use App\DataTables\Settings\Rate\Employee\RateDataTableEditor;
use App\Http\Controllers\Controller;

class EmployeeRateController extends Controller
{
    public function records(RateDataTable $dataTable)
    {
        return $dataTable->render('layouts.settings.rate.employee');
    }

    public function dtEditor(RateDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}