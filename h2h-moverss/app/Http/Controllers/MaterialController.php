<?php

namespace App\Http\Controllers;

use App\DataTables\Settings\Material\{GroupDataTable, GroupDataTableEditor, ItemDataTable, ItemDataTableEditor};
use App\Http\Requests\Order\OrderMaterialsRequest;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\{Material, Order};
use Exception, DB;

/**
 * Manage Materials in system.
 */
class MaterialController extends Controller
{
    /**
     * DataTable for output Materials page.
     * Show: Item Groups, and Items.
     * @param  GroupDataTable  $groups
     * @param  ItemDataTable  $items
     * @return Renderable
     */
    public function homeDT(GroupDataTable $groups, ItemDataTable $items): Renderable
    {
        return View('layouts.settings.materials.body', [
            'dtGroup' => $groups->html(),
            'dtItems' => $items->html(),
        ]);
    }

    /**
     * DataTable for showing Item Groups.
     * @param  GroupDataTable  $dataTable
     * @return mixed
     */
    public function groupDT(GroupDataTable $dataTable)
    {
        return $dataTable->render('layouts.app');
    }

    /**
     * DataTableEditor for Groups.
     * @param  GroupDataTableEditor  $editor
     * @return JsonResponse|mixed
     * @throws \Yajra\DataTables\DataTablesEditorException
     */
    public function groupDT_Editor(GroupDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    /**
     * DataTable for showing Items.
     * @param  ItemDataTable  $dataTable
     * @return mixed
     */
    public function recordsDT(ItemDataTable $dataTable)
    {
        return $dataTable->render('layouts.app');
    }

    /**
     * DataTableEditor for Items.
     * @param  ItemDataTableEditor  $editor
     * @return JsonResponse|mixed
     * @throws \Yajra\DataTables\DataTablesEditorException
     */
    public function goodsDT_Editor(ItemDataTableEditor $editor)
    {
        return $editor->process(request());
    }

    /**
     * Extra Materials response.
     * @param  Material  $material
     * @param  Request  $request
     * @return JsonResponse
     */
    public function records(Material $material, Request $request): JsonResponse
    {
        $records = $material->whereDivisionId($request->division_id)
            ->orderBy('sort')
            ->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records
            ]);
    }

    /**
     * Save Materials and customsExtras.
     * @param  OrderMaterialsRequest  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function saveRecords(OrderMaterialsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $order = Order::with('materials', 'customsExtras')->findOrFail($validated['order_id']);

        try {
            $res = [];
            DB::transaction(function () use (&$res, $order, $validated) {
                $changed = $order->updateMaterials($validated['records']);
                $changed += $order->updateCustomExtra($validated['custom_records']);

                if (!$changed) {
                    $res = [
                        'success' => true,
                        'msg' => 'Changed nothing',
                    ];
                } else {
                    $order->refresh();
                    $res = [
                        'success' => true,
                        'msg' => 'Materials changed',
                        'record' => $order
                    ];
                }
            });

            return response()
                ->json($res);
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }
    }

    public function reOrder(Request $request, Material $material)
    {
        $changed = 0;

        if (count($request->json()->all())) {
            $ids = $request->json()->all();
            foreach ($ids as $key) {
                $position = (int) $key['position'];

                $model = $material->findOrFail($key['id']);

                if ((int) $model->sort !== $position) {
                    $model->sort = $position;

                    $model->save();
                    $changed++;
                }
            }

            return response()->json([
                'success' => true,
                'changed' => $changed
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }
}
