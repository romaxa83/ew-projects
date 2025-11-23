<?php

namespace App\Http\Controllers\Import;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use Illuminate\Http\{JsonResponse, Request};
use Log;

class EquateMediaController extends Controller
{
    /**
     * Handle the incoming web hook request.
     *
     * @param  Request  $request  The incoming request object.
     * @return JsonResponse The JSON response indicating the success status.
     * @throws \JsonException When there is an error decoding the input JSON.
     */
    public function webHook(Request $request): JsonResponse
    {
        $inputJSON = $request->getContent();
        $payload = json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR)['data'];

        $division_id = $request->route()->getName() === 'webhook.EquateMediaCh' ? 1 : 2;
        $request->session()->put('division.id', $division_id);

        $source_id = Order\Source::query()
            ->whereJsonContains('division_ids', $division_id)
            ->where('title', 'LIKE', 'Equate%')
            ->first(['id'])->id;

        $order_data = [
            'source' => $source_id,
            'work' => [
                'date' => $payload['field_date'] ?? null,
            ],
            'client' => [
                'name' => $payload['field_first_name'],
                'lname' => $payload['field_last_name'],
                'phone' => $payload['field_phone'],
                'email' => $payload['field_e_mail'] ?? null,
            ],
            'pickup' => [
                'zip' => $payload['moving_from_zip'] ?? null,
                'elevator' => 0,
            ],
            'destination' => [
                'zip' => $payload['moving_to_zip'] ?? null,
                'elevator' => 0,
            ],
        ];

        $order = (new OrderController(new Order()))->createEmptyOrder($order_data);

        $notes = 'Order from EquateMedia:<br />
            Date: '.$payload['field_date'].'<br />'.
            (!empty($payload['field_size_of_move']) ? 'Size of move: '.$payload['field_size_of_move'].'<br />' : '').
            (!empty($payload['field_additional_comments']) ? ('Client notes: '.nl2br($payload['Description'])) : '');

        $note = new Order\Notes();
        $note->order_id = $order->id;
        $note->user_id = 0;
        $note->text = $notes;
        $note->is_pinned = 1;
        $note->save();

        $order->extended()->create([
            'import' => [
                'provider' => 'equatemedia',
            ],
        ]);


        $msg = 'New '.$request->route()->getName().' data received '.now()->toDateTimeString().' Order: '.$order->id;
        Log::info($msg, $payload);

        return response()
            ->json([
                'success' => true,
            ]);
    }
}
