<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\User\RouteList;
use Illuminate\Support\Facades\{Log, Route};

class UpdateRouteListController extends Controller
{
    /**
     * Обновить список URL для раздачи прав.
     */
    public function sync(): void
    {
        $route2id = $this->route2Id();
        $routes = Route::getRoutes();

        $skip_prefixes = ['_debugbar', '/dummy', '/webhook'];

        $ids = [];
        foreach ($routes as $route) {

            foreach ($route->methods as $method) {
                $v = [
                    'method' => $method,
                    'uri' => $route->uri,
                    'name' => $route->action['as'] ?? null,
                    'prefix' => $route->action['prefix'] ?? null,
                ];

                if ($method === 'HEAD' ||
                    in_array($v['prefix'], $skip_prefixes, true)) {
                    continue;
                }

                if (isset($route2id[$v['method']][$v['uri']]) && !isset($route2id[$v['method']][$v['uri']][$v['name']])) {
                    // Название роута поменялось
                    $r = RouteList::find(reset($route2id[$v['method']][$v['uri']]));
                    $r->name = $v['name'];
                    $r->save();

                    Log::channel('stdout')
                        ->info('route id: '.$r->id.' name changed -> '.$v['name']);
                    $id = $r->id;
                } elseif (isset($route2id[$v['method']][$v['uri']][$v['name']])) {
                    // Ничего не поменялось
                    $id = $route2id[$v['method']][$v['uri']][$v['name']];
                } else {
                    // Новый роут
                    $r = RouteList::create($v);
                    $id = $r->id;

                    Log::channel('stdout')
                        ->info('Created route id: '.$r->id.' name '.$v['name']);
                }
                $ids[] = $id;
            }
        }

        // Удаляем не существующие
        RouteList::whereNotIn('id', $ids)->where('is_group', 0)->delete();

        Log::channel('stdout')->info('Sync OK');
    }

    private function route2Id(): array
    {
        $old_routes = RouteList::get(['id', 'method', 'name', 'uri']);
        $data = [];
        foreach ($old_routes as $v) {
            $data[$v->method][$v->uri][$v->name] = $v->id;
        }

        return $data;
    }
}
