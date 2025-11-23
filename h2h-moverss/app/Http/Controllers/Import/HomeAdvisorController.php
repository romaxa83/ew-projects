<?php

namespace App\Http\Controllers\Import;

use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use Illuminate\Http\{JsonResponse, Request};

class HomeAdvisorController extends Controller
{
    /**
     * Коза, по факту не заюзано.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function webHook(Request $request): JsonResponse
    {
        $inputJSON = $request->getContent();
        $payload = json_decode($inputJSON, true);
        if (!$payload) {
            $payload = $request->all();
            $isPost = true;
        }

        file_put_contents(storage_path('app/public/homeAdvisor'), now()->toDateTimeString().PHP_EOL.
            'Data Type: '.(isset($isPost) ? 'POST' : 'JSON').PHP_EOL.
            print_r($payload, true).PHP_EOL.PHP_EOL, FILE_APPEND);

        resolve(Handler::class)->telegaMsg('New webhook homeAdvisor data received '.now()->toDateTimeString());

        return response()
            ->json([
                'success' => true,
            ]);
    }
}
