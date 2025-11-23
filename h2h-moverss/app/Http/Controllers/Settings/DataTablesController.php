<?php

namespace App\Http\Controllers\Settings;


/**
 * DataTables Простые выззовы.
 */

use App\Http\Controllers\Controller;
use App\Models\Order\{Status, StatusGroup};
use App\DataTables\Settings\Order\Status\{StatusDataTable, StatusDataTableEditor};
use App\DataTables\Settings\Order\StatusGroup\{StatusGroupDataTable, StatusGroupDataTableEditor};
use App\DataTables\Settings\Order\StatusRoute\{RecordsDataTable, RecordDataTableEditor};
use App\DataTables\Settings\Order\Source\{SourceDataTable, SourceDataTableEditor};

class DataTablesController extends Controller
{
    public function statusHome(StatusDataTable $dataTable)
    {
        return $dataTable->render('layouts.settings.order.status', [
            'groups' => StatusGroup::orderBy('title')->get(['id', 'title'])
        ]);
    }

    public function statusSave(StatusDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function sourceHome(SourceDataTable $dataTable)
    {
        return $dataTable->render('layouts.settings.order.source');
    }

    public function sourceSave(SourceDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function statusRouteHome(RecordsDataTable $dataTable)
    {
        return $dataTable->render('layouts.settings.order.status-route', [
            'statuses' => Status::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function statusRouteSave(RecordDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function statusGroupHome(StatusGroupDataTable $dataTable)
    {
        return $dataTable->render('layouts.settings.order.status-group');
    }

    public function statusGroupSave(StatusGroupDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}
