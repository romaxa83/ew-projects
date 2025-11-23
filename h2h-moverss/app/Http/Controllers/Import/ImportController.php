<?php

namespace App\Http\Controllers\Import;

use App\Exceptions\Handler;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Models\Order;
use DateTime, Exception, Log, DB;
use Illuminate\Http\{JsonResponse, Request};

class ImportController extends Controller
{
    /**
     * Handle the incoming web hook request.
     *
     * @param  Request  $request  The incoming request object.
     * @return JsonResponse The JSON response indicating the success status.
     * @throws \JsonException|\Throwable When there is an error decoding the input JSON.
     */
    public function webHook(Request $request): JsonResponse
    {
        try {
            $inputJSON = $request->getContent();
            $payload = !empty($inputJSON) ? json_decode($inputJSON, true, 512, JSON_THROW_ON_ERROR) : null;
            if (!$payload) {
                $payload = $request->all();
            }

            $source = str_replace('import.', '', $request->route()->getName());

            $division_id = 2;
            if (str_contains($source, '-IL')) {
                $division_id = 1;
            }

            $source = str_replace(['-IL', '-CA'], '', $source);

            $request->session()->put('division.id', $division_id);

            $source_id = Order\Source::query()
                ->whereJsonContains('division_ids', $division_id)
                ->where('title', 'LIKE', $source)
                ->first(['id'])?->id;

            throw_if(empty($payload['client_first_name']), 'RuntimeException', 'Empty Client Name');
            throw_if(empty($payload['client_phone']), 'RuntimeException', 'Empty Phone number');
            throw_if(!empty($payload['move_date']) && !$this->validateDate($payload['move_date'], 'Y-m-d'),
                'RuntimeException', 'Invalid date format. Y-m-d is required');


            $order_data = [
                'source' => $source_id,
                'work' => [
                    'date' => $payload['move_date'] ?? null,
                ],
                'client' => [
                    'name' => $payload['client_first_name'],
                    'lname' => $payload['client_last_name'] ?? null,
                    'phone' => $payload['client_phone'],
                    'email' => $payload['client_email'] ?? null,
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

            DB::transaction(function () use ($source, $order_data, $payload, &$order) {

                $order = (new OrderController(new Order()))->createEmptyOrder($order_data);

                $notes = 'Order from '.$source.':<br />
                    Date: '.($payload['move_date'] ?? 'n/a').'<br />'.
                    (!empty($payload['size_of_move']) ? 'Size of move: '.$payload['size_of_move'].'<br />' : '').
                    (!empty($payload['notes']) ? ('Client notes: '.nl2br($payload['notes'])) : '');

                $note = new Order\Notes();
                $note->order_id = $order->id;
                $note->user_id = 0;
                $note->text = $notes;
                $note->is_pinned = 1;
                $note->save();

                $order->extended()->create([
                    'import' => [
                        'provider' => $source,
                    ],
                ]);
            });


            $msg = 'New '.$request->route()->getName().' data received '.now()->toDateTimeString().' Order: '.$order->id;
            Log::info($msg, $payload);

            resolve(Handler::class)->telegaMsg('New webhook '.$source.' data received '.now()->toDateTimeString());

            return response()
                ->json([
                    'success' => true,
                ]);
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage(),
                ]);
        }
    }

    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
