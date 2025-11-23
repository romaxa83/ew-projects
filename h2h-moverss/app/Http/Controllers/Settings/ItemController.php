<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\DataTables\Settings\Item\{
    GroupDataTable,
    GroupDataTableEditor,
    ItemDataTable,
    ItemDataTableEditor
};

class ItemController extends Controller
{

    public function home(GroupDataTable $groups, ItemDataTable $items)
    {
        return View('layouts.settings.items.body', [
            'dtGroup' => $groups->html(),
            'dtItems' => $items->html(),
        ]);
    }

    public function groupDT(GroupDataTable $dataTable)
    {
        return $dataTable->render('layouts.app');
    }

    public function groupDT_Editor(GroupDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    public function goodsDT(ItemDataTable $dataTable)
    {
        return $dataTable->render('layouts.app');
    }

    public function goodsDT_Editor(ItemDataTableEditor $editor)
    {
        return $editor->process(request());
    }
}
