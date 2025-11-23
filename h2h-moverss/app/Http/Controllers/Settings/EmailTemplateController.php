<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Settings\EmailTemplate;
use App\Models\Settings\EmailTemplateGroup;
use App\Utils\StringBlade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\{JsonResponse, Request};
use Mailjet\{Client, Resources};
use DB, Exception;

class EmailTemplateController extends Controller
{

    public function ajaxMandrillTemplates()
    {

    }


    public function ajaxMailjetTemplates(Request $request)
    {
//        $records =
//            EmailTemplate::whereDivisionId($request->session()->get('division.id'))->where('active', 1)->orderBy('sort')->get();

        $records = EmailTemplateGroup::where('active', 1)->with('groupRecords', function (HasMany $q) {
            return $q->where('active', 1)->orderBy('sort');
        })->orderBy('sort')->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
            ]);

    }


    public function ajaxInfo(Request $request): JsonResponse
    {
        $records = EmailTemplate::whereDivisionId($request->session()->get('division.id'))->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
            ]);
    }

    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'nullable|integer|exists:email_templates,id',
            'active' => 'boolean',
            'title' => 'required|string|max:70',
            'sort' => 'nullable|integer',
            'mailjet_tpl_id' => 'required|integer',
        ]);

        if ($validated['id']) {
            $record = EmailTemplate::findOrFail($validated['id']);
        } else {
            $record = new EmailTemplate();
        }

        $record->active = !empty($validated['active']);
        $record->sort = $validated['sort'];
        $record->title = strip_tags($validated['title']);
        $record->mailjet_tpl_id = (int)$validated['mailjet_tpl_id'];
        $record->division_id = $request->session()->get('division.id');

        $record->save();

        $records = EmailTemplate::whereDivisionId($request->session()->get('division.id'))->get();
        return response()
            ->json([
                'success' => true,
                'records' => $records
            ]);
    }

    /**
     * Отправка email уведомления по шаблону.
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tpl_id' => 'required|integer|exists:email_templates,id',
            'order_id' => 'required|integer|exists:orders,id',
            'is_render' => 'nullable|boolean', // Отрендерить
            'html' => 'nullable|string',
            'subject' => 'nullable|string',
            'to' => 'nullable|string',
            'reply_to' => 'nullable|string',
        ]);

        $tpl = EmailTemplate::find($validated['tpl_id']);
        $order = Order::withWaypointsFormat()
            ->withWorksFormat()
            ->with([
                'manager:id,name,email',
                'manager.employee',
                'client.emails' => function ($q) {
                    return $q
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'client.phones' => function ($q) {
                    return $q
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'estimate',
            ])
            ->find($validated['order_id']);

        $order->load([
            'estimate.' . $order->estimate->type,
        ]);

        $order->href = route('customer.orderPublicView', ['hash' => $order->hash]);

        $order->first_waypoint = $order->waypoints->first();
        $order->first_work = $order->works->first();

        // Конвертим когда начало работы
        if ($order->first_work) {
            if ($order->first_work->start_date && $order->first_work->start_time && $order->first_work->start_time_to) {
                $t_1 = Carbon::parse($order->first_work->start_time)->format('g:i A');
                $t_2 = Carbon::parse($order->first_work->start_time_to)->format('g:i A');
                $date = Carbon::parse($order->first_work->start_date)->format('m/d/Y');

                $friendly_date = $date . ' between (' . $t_1 . ' - ' . $t_2 . ') Time Arrival Window';
            } elseif ($order->first_work->start_date && $order->first_work->start_time) {
                $friendly_date = Carbon::parse($order->first_work->start_date . ' ' . $order->first_work->start_time)->format('m/d/Y \a\t g:i A') . ' Time Arrival';
            } elseif ($order->first_work->start_date) {
                $friendly_date = Carbon::parse($order->first_work->start_date)->format('m/d/Y');
            }
            $order->first_work->friendly_date = $friendly_date ?? null;
        }


        try {
            $mj3 = new Client(config('app.mail_jet.public'), config('app.mail_jet.private'), true, ['version' => 'v3']);
            $mj = new Client(config('app.mail_jet.public'), config('app.mail_jet.private'), true, ['version' => 'v3.1']);

            $mj3->setConnectionTimeout(10);
            $mj3->setTimeout(300);

            $response = $mj3->get(Resources::$TemplateDetailcontent, ['id' => $tpl->mailjet_tpl_id]);
            if (!$response->success()) {
                return response()
                    ->json([
                        'success' => false,
                        'msg' => 'Mailjet API GW error: status: '.$response->getStatus(),
                    ]);
            }
            $template = $response->getBody();

            $email = $order->client->emails->first();
            $html = app(StringBlade::class)->render($template['Data'][0]['Html-part'], [
                'order' => $order->toArray(),
                'client' => $order->client->toArray(),
                'manager' => isset($order->manager->employee) ? $order->manager->employee->toArray() : [],
                'noteLength' => !empty($validated['text']) ? strlen($validated['text']) : 0,
            ]);

            $subject = $template['Data'][0]['Headers']['Subject'];
            $subject = str_replace('%client_name%', $order->client->name, $subject);

            if (!empty($validated['is_render'])) {
                return response()
                    ->json([
                        'success' => true,
                        'data' => $html,
                        'meta' => [
                            'subject' => $subject,
                            'to' => [
                                'email' => $email->value ?? null,
                                'title' => $order->client->name
                            ],
                            'reply-to' => $order->manager->email ?? null,
                        ]
                    ]);
            }


            $to_emails = preg_split('/(\s|,|\|;)/', $validated['to']);
            foreach ($to_emails as $k => &$email) {
                $email = trim($email);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    unset($to_emails[$k]);
                }
            }

            if (!count($to_emails)) {
                throw new Exception('Incorrect Recipients Email');
            }

            if (empty($validated['html'])) {
                throw new Exception('Empty message');
            }
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage().' Line: '.$e->getLine()
                ]);
        }

        // Отправка
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => 'info@h2hmove.com',
                        'Name' => $order->manager->name ?? 'Manager'
                    ],
                    'Subject' => $validated['subject'],
                    'HTMLPart' => $validated['html']
                ]
            ]
        ];

        foreach ($to_emails as $v) {
            $body['Messages'][0]['To'][] = [
                'Email' => $v,
                'Name' => $order->client->name
            ];
        }

        // Обработка ReplyTo
        $reply = '';
        if (!empty($validated['reply_to']) && filter_var($validated['reply_to'], FILTER_VALIDATE_EMAIL)) {
            $reply = $validated['reply_to'];
        } elseif (isset($order->manager)) {
            $reply = $order->manager->email;
        }

        if ($reply) {
            $body['Messages'][0]['ReplyTo'] = [
                'Email' => $reply,
                'Name' => $order->manager->name ?? 'Manager'
            ];
        }


        $response = $mj->post(Resources::$Email, ['body' => $body]);
        if ($response->success()) {
            $response = $response->getData();

            foreach ($to_emails as $k => $v) {
                $order->addActivity('email', [
                    'provider' => 'mailjet',
                    'to' => $v,
                    'text' => 'Template: ' . $tpl->title,
                    'template_id' => $tpl->id,
                    'client_id' => $order->client->id,
                    'events' => [],
                    'ext_id' => $response['Messages'][0]['To'][$k]['MessageID'],
                ]);
            }

            return response()
                ->json([
                    'success' => true,
                    'msg' => 'Message successfully sent',
                    'data' => $response,
                ]);
        }

        return response()
            ->json([
                'success' => false,
                'msg' => 'Error on mail gateway, try again later',
                'data' => $response->getData(),
            ]);
    }

    /**
     * Принимаем данные с MailJet.
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function mailJetWebHook(Request $request): JsonResponse
    {
        $inputJSON = $request->getContent();
        $payload = json_decode($inputJSON, true);

        DB::transaction(function () use ($payload) {
            foreach ($payload as $v) {
                $act = Order\Activity::where('ext_id', $v['MessageID'])->first();
                if ($act) {
                    $miscs = $act->miscs;

                    $miscs['events'][$v['event']] = [
                        'date' => date('Y-m-d H:i:s', $v['time']),
                    ];
                    $act->miscs = $miscs;
                    $act->save();
                }
            }
        });

        return response()
            ->json([
                'success' => true,
            ]);
    }

}
