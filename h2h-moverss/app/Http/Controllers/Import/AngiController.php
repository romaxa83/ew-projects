<?php

namespace App\Http\Controllers\Import;

use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use Illuminate\Http\{JsonResponse, Request};
use Log;

class AngiController extends Controller
{
    /**
     * Process the web hook request.
     *
     * @param  Request  $request  The HTTP request object.
     * @return JsonResponse The JSON response indicating the success of the web hook processing.
     * @throws \JsonException If JSON decoding encounters an error.
     */
    public function webHook(Request $request): JsonResponse
    {$inputJSON = $request->getContent();
        $payload = json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR);

        $division_id = $request->route()->getName() === 'webhook.AngiCh' ? 1 : 2;
        $request->session()->put('division.id', $division_id);

        $address = $payload['PostalAddress']['AddressFirstLine'];
        if (!empty($payload['PostalAddress']['AddressSecondLine'])) {
            $address = $payload['PostalAddress']['AddressSecondLine'];
        }

        $source_id = Order\Source::query()
            ->whereJsonContains('division_ids', $division_id)
            ->where('title', 'LIKE', 'Angie%')
            ->first(['id'])->id;

        $order_data = [
            'source' => $source_id,
            'client' => [
                'name' => $payload['FirstName'],
                'lname' => $payload['LastName'],
                'phone' => $payload['PhoneNumber'],
                'email' => $payload['Email'] ?? null,
            ],
            'pickup' => [
                'zip' => $payload['PostalAddress']['PostalCode'],
                'address' => "$address, {$payload['PostalAddress']['City']}, {$payload['PostalAddress']['State']} {$payload['PostalAddress']['PostalCode']}, USA",
                'elevator' => 0,
            ],
        ];

        $order = (new OrderController(new Order()))->createEmptyOrder($order_data);

        $notes = 'Order from Angi:<br />'.
            'DATA: '.print_r($payload, true);

        $note = new Order\Notes();
        $note->order_id = $order->id;
        $note->user_id = 0;
        $note->text = $notes;
        $note->is_pinned = 1;
        $note->save();

        $order->extended()->create([
            'import' => [
                'provider' => 'angi',
                'id' => $payload['CorrelationId'],
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
