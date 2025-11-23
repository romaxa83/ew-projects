<?php

namespace App\Http\Controllers\Emails;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Settings\WaypointFlights;
use App\Services\Emails\MailchimpService;
use Carbon\Carbon;
use GuzzleHttp\Exception\{ServerException, RequestException};
use Illuminate\Http\Request;
use Exception;
use MailchimpTransactional\{ApiClient, ApiException, Api\MessagesApi, Api\TemplatesApi};
use App\Models\Settings\EmailTemplate;

class MailchimpController extends Controller
{

    /**
     * @var $MailchimpApi ApiClient
     */
    private $MailchimpApi;
    /**
     * @var $MessagesApi MessagesApi
     */
    public $MessagesApi;
    /**
     * @var $TemplatesApi TemplatesApi
     */
    public $TemplatesApi;


    private function initApi()
    {
        $this->MailchimpApi = new ApiClient();
//        $this->MailchimpApi->setTimeout(3000);
        $divisionMiscs = session()->get('division.miscs');
        if (empty($divisionMiscs['mailchimp_api_key']))
            throw new Exception('Mailchimp (Mandrill) API key is not set!');
        $this->MailchimpApi->setApiKey($divisionMiscs['mailchimp_api_key']);
        $this->MessagesApi = $this->MailchimpApi->messages;
        $this->TemplatesApi = $this->MailchimpApi->templates;

    }


    // #todo moving to service (MailchimpService), look and late delete
//    private function getMergeVarsValue(Order $Order)
//    {
//        $works = [];
//        $moving_dt = '';
//        if ($Order->works) {
//            $Works = $Order->works->filter(function ($work) {
//                if (!$work->workTypes->count())
//                    return null;
//
//                foreach ($work->workTypes as $type) {
//                    if ($type->pivot && $type->pivot->work_type_id == 1) {
//                        return true;
//                    }
//                }
//                return null;
//            });
//            if ($Works->isNotEmpty()) {
//                $Work = $Works->sortBy(function ($work) {
//                    return (new Carbon($work->start_date . ' ' . $work->start_time))->getTimestamp();
//                })->first();
//                $moving_dt = (new Carbon($Work->start_date . ' ' . $Work->start_time))->format("M d, Y \a\\t g:i A");
//            } else {
//                $Work = $Order->works->sortBy(function ($work) {
//                    return (new Carbon($work->start_date . ' ' . $work->start_time))->getTimestamp();
//                })->first();
//                $moving_dt = (new Carbon($Work->start_date . ' ' . $Work->start_time))->format("M d, Y \a\\t g:i A");
//            }
//
//            foreach ($Order->works as $work) {
//
//                $WORK = ['TYPE' => '', 'DATE' => '', 'TIME' => ''];
//                $WORK['TYPE'] = $work->workTypes->implode('title', ', ');
//                // TODO
//                $WORK['START'] = (new \DateTime($work->start_date))->format('M d, Y');  //'M d, Y
//                $WORK['TIME'] = (new \DateTime($work->start_time))->format('g:i A'); //'g:i A'
//                $works[] = $WORK;
//            }
//        }
////        $waypoints = [];
//        $waypoints_origin_first_address = '';
//        $waypoints_origin_first_zip = '';
//        $waypoints_origin_address = '';
//        $waypoints_origin_zip = '';
//        $waypoints_origin_stairs_flights = '';
//        $waypoints_origin_has_elevator = '';
//
//        $waypoints_destination_address = '';
//        $waypoints_destination_zip = '';
//        $waypoints_destination_has_elevator = '';
//        $waypoints_destination_stairs_flights = '';
//        if ($Order->waypoints) {
//            if(
//                $first_waypoint = $Order
//                    ->waypoints
//                    ->where('type', 'pickup')
//                    ->sortBy('created_at')
//                    ->first()
//            ){
//                $waypoints_origin_first_address = $first_waypoint->address;
//                $waypoints_origin_first_zip = $first_waypoint->zip;
//            }
//
//            foreach ($Order->waypoints as $waypoint) {
//                if ($waypoint->type == 'pickup') {
//                    $waypoints_origin_address = $waypoint->address;
//                    $waypoints_origin_zip = $waypoint->zip;
//                    $waypoints_origin_has_elevator = $waypoint->has_elevator ? 'yes' : 'no';
//                    $waypoints_origin_stairs_flights = $waypoint->flights_id;
//                }
//                if ($waypoint->type == 'destination') {
//                    $waypoints_destination_address = $waypoint->address;
//                    $waypoints_destination_zip = $waypoint->zip;
//                    $waypoints_destination_has_elevator = $waypoint->has_elevator ? 'yes' : 'no';
//                    $waypoints_destination_stairs_flights = $waypoint->flights_id;
//                }
//            }
//        }
//        if (!empty($waypoints_origin_stairs_flights)) {
//            $WaypointFlight = WaypointFlights::find($waypoints_origin_stairs_flights);
//            $waypoints_origin_stairs_flights = $WaypointFlight ? $WaypointFlight->title : '';
//        } else {
//            $waypoints_origin_stairs_flights = '';
//        }
//        if (!empty($waypoints_origin_zip) && preg_match('/(.*)( USA)$/', $waypoints_origin_address)) {
//            $waypoints_origin_address = preg_replace('/(.*)( USA)$/', '$1 ' . $waypoints_origin_zip, $waypoints_origin_address);
//        }
//        if (!empty($waypoints_destination_zip) && preg_match('/(.*)( USA)$/', $waypoints_destination_address)) {
//            $waypoints_destination_address = preg_replace('/(.*)( USA)$/', '$1 ' . $waypoints_destination_zip, $waypoints_destination_address);
//        }
//        if (!empty($waypoints_origin_first_zip) && preg_match('/(.*)( USA)$/', $waypoints_origin_first_address)) {
//            $waypoints_origin_first_address = preg_replace('/(.*)( USA)$/', '$1 ' . $waypoints_origin_first_zip, $waypoints_origin_first_address);
//        }
//
//        return [
//            'ORDER_ID' => $Order->id,
//            'CLIENT_NAME' => $Order->client ? $Order->client->name . ' ' . $Order->client->lname : '',
//            'CLIENT_FIRST_NAME' => $Order->client ? $Order->client->name : '',
//            'CLIENT_LAST_NAME' => $Order->client ? $Order->client->lname : '',
//            'CUSTOMER_PAGE_URL' => config('app.url') . '/customer/order/' . $Order->hash,
////            'WORKS' => $works,
//            // $Order->manager->employee ?
//            'MANAGER_CURRENT_NAME' => auth_user()?->employee->full_name,
//            'MANAGER_NAME' => $Order->manager->name,
//            'MANAGER_EMAIL' => $Order->manager->email,
//            'WAYPOINT_ORIGIN_FIRST_ADDRESS' => $waypoints_origin_first_address,
//            'WAYPOINT_ORIGIN_ADDRESS' => $waypoints_origin_address,
//            'WAYPOINT_PICKUP_ADDRESS' => $waypoints_origin_address,
//            'WAYPOINT_DESTINATION_ADDRESS' => $waypoints_destination_address,
//            'WAYPOINT_ORIGIN_STAIRS_FLIGHTS' => $waypoints_origin_stairs_flights,
//            'WAYPOINT_PICKUP_STAIRS_FLIGHTS' => $waypoints_origin_stairs_flights,
//            'WAYPOINT_DESTINATION_STAIRS_FLIGHTS' => $waypoints_destination_stairs_flights,
//            'MOVING_FIRST_DATETIME' => $moving_dt,
//            'WAYPOINT_ORIGIN_HAS_ELEVATOR' => $waypoints_origin_has_elevator,
//            'WAYPOINT_DESTINATION_HAS_ELEVATOR' => $waypoints_destination_has_elevator
//        ];
//    }

    // #todo moving to service (MailchimpService), look and late delete
//    private function prepareMergeVars(Order $Order)
//    {
//        $vars = [
//            'CLIENT_NAME',
//            'CLIENT_FIRST_NAME',
//            'CLIENT_LAST_NAME',
//            'CUSTOMER_PAGE_URL',
//            'MANAGER_CURRENT_NAME',
//            'MANAGER_NAME',
//            'MANAGER_EMAIL',
//            'ORDER_ID',
//            'WAYPOINT_ORIGIN_FIRST_ADDRESS',
//            'WAYPOINT_ORIGIN_ADDRESS',
//            'WAYPOINT_ORIGIN_HAS_ELEVATOR',
//            'WAYPOINT_PICKUP_ADDRESS',
//            'WAYPOINT_DESTINATION_ADDRESS',
//            'WAYPOINT_ORIGIN_STAIRS_FLIGHTS',
//            'WAYPOINT_PICKUP_STAIRS_FLIGHTS',
//            'WAYPOINT_DESTINATION_STAIRS_FLIGHTS',
//            'WAYPOINT_DESTINATION_HAS_ELEVATOR',
//            'MOVING_FIRST_DATETIME'
//        ];
//        $mergeVarsValue = $this->getMergeVarsValue($Order);
//
//        $merge_vars = [];
//        if (!empty($vars)) {
//            foreach ($vars as &$var) {
//                $var = strtoupper(trim($var));
//                $merge_vars[] = [
//                    'name' => $var,
//                    'content' => !empty($mergeVarsValue[$var]) ? $mergeVarsValue[$var] : ''
//                ];
//            }
//        }
//        return $merge_vars;
//    }


    public function getTemplates()
    {

        try {
            $response = [
                'success' => false
            ];
            $this->initApi();
            $currentDivisionLabel = 'fake-empty-label';

            $divisionMiscs = session()->get('division.miscs');
            if (!empty($divisionMiscs['mandrill_templates_label']))
                $currentDivisionLabel = $divisionMiscs['mandrill_templates_label'];

            $templates = $this->TemplatesApi->list(['label' => $currentDivisionLabel]);

            if ($templates instanceof Exception)
                throw $templates;

            $response['success'] = true;
            $response['records'] = $templates;

        } catch (ApiException $e) {
            $response['msg'] = 'ApiException: ' . $e->getMessage();
        } catch (RequestException $e) {
            $response['msg'] = 'RequestException: ' . $e->getMessage();
        } catch (ServerException $e) {
            $response['msg'] = 'Guzzle ServerException: ' . $e->getMessage();
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return response()->json($response);
    }


    public function sendMandrillTemplate(Request $request)
    {
        try {
            $this->initApi();
            $validated = $request->validate([
                'tpl_id' => 'required',
                'order_id' => 'required|integer|exists:orders,id',
                'html' => 'nullable|string',
                'subject' => 'nullable|string',
                'to' => 'nullable|string',
                'reply_to' => 'nullable|string',
            ]);
            $divisionMiscs = session()->get('division.miscs');
//            /**
//             * @var $templateInfo \stdClass
//             */
//            $templateInfo = $this->TemplatesApi->info(['name' => $validated['tpl_slug']]);
//            if ($templateInfo instanceof Exception)
//                throw $templateInfo;
            /**
             * @var $sended \stdClass
             */

            $sended = $this->MessagesApi->send([
                'message' => [
                    'html' => $validated['html'],
                    'subject' => $validated['subject'],
                    'to' => [
                        [
                            'email' => $validated['to']
                        ]
                    ],
                    'from_email' => $divisionMiscs['mandrill_from_email'],
                    'from_name' => $divisionMiscs['mandrill_from_name'],
                    'headers' => [
                        'Reply-To' => $validated['reply_to']
                    ]
                ]
            ]);
            if ($sended instanceof Exception)
                throw $sended;

            // Add activity
            $Order = Order::find($validated['order_id']);
            foreach ($sended as $v) {
                if ($v->status != 'sent' && $v->status != 'queued') {
                    throw new Exception('Sending status is ' . $v->status . '. Reject reason is ' . $v->reject_reason);
                }
                $Order->addActivity('email', [
                    'provider' => 'mandrill',
                    'to' => $v->email,
                    'text' => 'Template: ' . $validated['tpl_id'],
                    'template_id' => $validated['tpl_id'],
                    'client_id' => $Order->client->id,
                    'events' => [],
                    'ext_id' => $v->_id,
                ]);
            }

            $response = ['success' => true, 'status' => $sended];
        } catch (ApiException $e) {
            $response['msg'] = 'ApiException: ' . $e->getMessage();
        } catch (RequestException $e) {
            $response['msg'] = 'RequestException: ' . $e->getMessage();
        } catch (ServerException $e) {
            $response['msg'] = 'Guzzle ServerException: ' . $e->getMessage();
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return response()->json($response);
    }


    public function renderMandrillTemplate(Request $request)
    {
        try {
            $response = [
                'success' => false
            ];
            $this->initApi();
            $validated = $request->validate([
                'orderID' => 'required|integer|exists:orders,id',
                'tpl_slug' => 'required',
            ]);
//            $template = EmailTemplate::with('mandrill')->find($validated['tpl_id']);
            $Order = Order::mandrillTemplateVars()->findOrFail($validated['orderID']);
            if (!$Order->manager) {
                throw new Exception("Order #{$validated['orderID']} not assigned to manager! Assign it and try again");
            }

            /** @var MailchimpService $service */
            $service = resolve(MailchimpService::class);
            $mergeVars = $service->prepareMergeVars($Order);
//            $mergeVars = $this->prepareMergeVars($Order);

            /**
             * @var $templateInfo \stdClass
             */
            $templateInfo = $this->TemplatesApi->info(['name' => $validated['tpl_slug']]);
            if ($templateInfo instanceof Exception)
                throw $templateInfo;
            /**
             * @var $rendered \stdClass
             */

            $rendered = $this->TemplatesApi->render([
                'template_name' => $validated['tpl_slug'],
                'template_content' => [['name' => $validated['tpl_slug'], 'content' => $templateInfo->code]],
                'merge_vars' => $mergeVars
            ]);
            if ($rendered instanceof Exception)
                throw $rendered;

            $response['success'] = true;
            $response['template'] = ['html' => $rendered->html];
            $response['rendered'] = $rendered;

        } catch (ApiException $e) {
            $response['msg'] = 'ApiException: ' . $e->getMessage();
        } catch (RequestException $e) {
            $response['msg'] = 'RequestException: ' . $e->getMessage();
        } catch (ServerException $e) {
            $response['msg'] = 'Guzzle ServerException: ' . $e->getMessage();
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return response()->json($response);
    }

    // todo delete

//    public function test_send()
//    {
//        try {
//            $this->initApi();
//            $template = EmailTemplate::with('mandrill')->find(1);
//            $Order = Order::mandrillTemplateVars()->findOrFail(1);
//            $mergeVars = $this->prepareMergeVars($Order);
////            dd([
////                'template_name' =>$template->mandrill->template_slug,
////                'template_content' => [$mergeVars]
////            ]);
//            /**
//             * @var $templateInfo \stdClass
//             */
//            $templateInfo = $this->TemplatesApi->info(['name' => $template->mandrill->template_slug]);
//            if ($templateInfo instanceof Exception)
//                throw $templateInfo;
//            /**
//             * @var $rendered \stdClass
//             */
//
//            dump($mergeVars);
//            $rendered = $this->TemplatesApi->render([
//                'template_name' => $template->mandrill->template_slug,
//                'template_content' => [['name' => $template->mandrill->template_slug, 'content' => $templateInfo->code]],
//                'merge_vars' => $mergeVars
//            ]);
//            if ($rendered instanceof Exception)
//                throw $rendered;
//
//            return response($rendered->html, 200, ['Content-Type' => 'text/html']);
//
//        } catch (ApiException $e) {
//            echo 'ApiException: ', $e->getMessage(), "\n";
//        } catch (RequestException $e) {
//            echo 'Guzzle RequestException:';
//            dump($e->getResponse()->getBody()->getContents());
////            echo 'Guzzle RequestException: ', $e->getMessage(), "\n";
//        } catch (ServerException $e) {
//            echo 'Guzzle ServerException:';
//            dump($e->getResponse()->getBody()->getContents());
////            echo 'Guzzle ServerException: ', $e->getMessage(), "\n";
//        } catch (Exception $e) {
//            echo 'Exception: ', $e->getMessage(), "\n";
//        }
//
//
//    }
//
//    public function test()
//    {
//        try {
//            //$this->initApi();
////            $this->Mailchimp->messages->send();
//            $response = $this->MailchimpApi->users->ping();
//            dump($response);
//        } catch (ApiException $e) {
//            echo 'ApiException: ', $e->getMessage(), "\n";
//        } catch (Exception $e) {
//            echo 'Exception: ', $e->getMessage(), "\n";
//        }
//    }

}
