<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Order\Tag as OrderTag;
use App\Models\Client\Tag as ClientTag;
use Illuminate\Http\{JsonResponse, Request};

class TagsController extends Controller
{

    /**
     * Get tags.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getRecords(Request $request): JsonResponse
    {
        $request->validate([
            'section' => 'required|in:orders,clients',
        ]);

        $section = $request->section;
        $model = null;
        if ($section === 'orders') {
            $model = OrderTag::withCount('orders');
        } elseif ($section === 'clients') {
            $model = ClientTag::withCount('clients');
        }

        return response()
            ->json([
                'success' => true,
                'records' => $model->get(),
            ]);
    }

    /**
     * Save Tags for clients/orders.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function saveRecords(Request $request): JsonResponse
    {
        $request->validate([
            'section' => 'required|in:orders,clients',
            'records.*.title' => 'required|string|min:1|max:60',
            'records.*.icon' => 'nullable|string|max:30',
            'records.*.color' => 'nullable|string|max:7',
            'records.*.sort' => 'required|integer|min:1|max:50',
        ]);

        $section = $request->section;
        $model = null;
        if ($section === 'orders') {
            $model = new OrderTag();
        } elseif ($section === 'clients') {
            $model = new ClientTag();
        }

        $exists_ids = [];
        foreach ($request->get('records', []) as $v) {
            $v['title'] = strip_tags($v['title']);
            $v['icon'] = strip_tags($v['icon']);
            $v['color'] = strip_tags($v['color']);

            $tag = $model->updateOrCreate([
                'id' => $v['id'],
            ], $v);
            $exists_ids[] = $tag->id;
        }

        // Delete removed tags
        $model->query()
            ->whereNotIn('id', $exists_ids)
            ->get('id')
            ->each(function ($tag) {
                // No need detach from relations
//                if ($section === 'orders') {
//                    $tag->orders()->detach();
//                } elseif ($section === 'clients') {
//                    $tag->clients()->detach();
//                }
                $tag->delete();
            });


        return response()
            ->json([
                'success' => true,
                'msg' => 'Successfully updated',
            ]);
    }
}
