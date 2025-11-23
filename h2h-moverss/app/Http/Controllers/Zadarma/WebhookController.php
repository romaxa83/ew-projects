<?php

namespace App\Http\Controllers\Zadarma;

use App\Events\Communications\EmployeeStatus;
use App\Events\TwilioSmsEvent;
use App\Events\ZadarmaCallEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\OrderController;
use App\Services\Communications\RecordCreateService;
use App\Services\Employees\CommunicationStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Zadarma_API\Webhook\Request as WebhookRequest;
use Zadarma_API\Api;
use Zadarma_API\Webhook\{AbstractNotify, NotifyInternal, NotifyIvr, NotifyStart, NotifyEnd, NotifyOutEnd, NotifyOutStart, NotifyRecord, NotifyAnswer};
use App\Models\{Client\Phone,
    Communications\CommunicationRecord,
    Employee,
    Employee\PbxData,
    Twilio\TwilioSms,
    Zadarma\CallsEvents,
    Division,
    Order,
    Zadarma\CallsRecords,
    Zadarma\SmsEvents};
use Exception;

class WebhookController extends Controller
{

    /**
     * @var $Api Api
     */
    protected $Api = null;
    protected $pbxID = null;
//    protected $orderTouchDesposition = [
//        'busy', 'cancel', 'no answer', 'failed', 'no money', 'unallocated number', ''
//    ];

    public function echo($division_id)
    {
        try {
            $this->initZadarmaApi($division_id);

//            logger_zadarma('ECHO INIT');

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'exception' => $e->getMessage()
            ]);
        }

        if (isset($_GET['zd_echo']))
            exit($_GET['zd_echo']);

        return response()->json(null);
    }


    private function getNotify($division_id)
    {
//        logger_zadarma('GET NOTIFY' , ['division_id' => $division_id]);
        $Api = $this->initZadarmaApi($division_id);
        // пытаемся слоавить стоковые ивенты
        $Notify = $Api->getWebhookEvent();
        if (!$Notify)
            $Notify = $this->getCustomEvents($Api);
        return $Notify;
    }

    private function getCustomEvents(Api $Api)
    {
        $Notify = null;
        $postData = $_POST;

//        logger_zadarma('GET CUSTOM EVENTS' , ['postDATA' => $postData]);

        // check broken headers events
        if (!empty($postData['event'])) {
            switch ($postData['event']) {
                case AbstractNotify::EVENT_START:
                    $Notify = new NotifyStart($postData);
                    break;

                case AbstractNotify::EVENT_IVR:
                    $Notify = new NotifyIvr($postData);
                    break;

                case AbstractNotify::EVENT_INTERNAL:
                    $Notify = new NotifyInternal($postData);
                    break;

                case AbstractNotify::EVENT_ANSWER:
                    $Notify = new NotifyAnswer($postData);
                    break;

                case AbstractNotify::EVENT_END:
                    $Notify = new NotifyEnd($postData);
                    break;

                case AbstractNotify::EVENT_OUT_START:
                    $Notify = new NotifyOutStart($postData);
                    break;

                case AbstractNotify::EVENT_OUT_END:
                    $Notify = new NotifyOutEnd($postData);
                    break;

                case AbstractNotify::EVENT_RECORD:
                    $Notify = new NotifyRecord($postData);
                    break;
                case 'SMS':
                    $Notify = new NotifySMS($postData);
                    break;
                default:
                    $Notify = null;
            }
            return $Notify;
        }

        // not actual
        $headers = getallheaders();
        if (empty($headers['Signature'])) {
            return null;
        } else {
            $signature = $headers['Signature'];
        }

        if ($postData['event'] == 'SMS') {
            $Notify = new NotifySMS($postData);
        }

        if ($Notify && $signature != $Api->encodeSignature($Notify->getSignatureString())) {
            return null;
        }
        return $Notify;
    }

    public function testCall()
    {
//        logger_zadarma('TEST CALL');

        $callEvent = CallsEvents::find(15297);
        $callEvent->event = 'NOTIFY_ANSWER';
        //$callEvent->caller_id = '13104895152';
//        $Event->direction = 'inbound';
//        $Event->from = $Event->to;
        dump($callEvent->toArray());
        broadcast(new ZadarmaCallEvent($callEvent, 2));
        dump('broadcasted');
    }


    public function catchEvents($division_id)
    {
        try {
            // если надо отправим команду
            $reponseRequest = new WebhookRequest();
            $Notify = $this->getNotify($division_id);

//            logger_zadarma('CATCH EVENTS', [
//                'event' => $Notify->event,
//                'notify' => $Notify
//            ]);

            if (!$Notify) {
                throw new Exception('Notify is empty! Mayby signature Problem! $_POST = ' . print_r($_POST, 1));
            }

            $tmp_p = '/home/ally/domains/beta.allymovers.com/storage/app/';
            $filtered = array_filter(
                $Notify->toArray(),
                fn($key) => in_array($key, (new CallsEvents)->getFillable()),
                ARRAY_FILTER_USE_KEY
            );
//            file_put_contents($tmp_p . 'zadarma.log',
//                PHP_EOL . date('Y-m-d H:i:s') . '--- Notify array' . print_r($Notify->toArray(), true) . '----' . PHP_EOL
//                . '-----Filtered array' . print_r($filtered, 1) . PHP_EOL, FILE_APPEND);

//            logger_zadarma('CATCH EVENTS', [
//                '$filtered' => $filtered
//            ]);

            //
            if (
                $Notify->event == AbstractNotify::EVENT_START
                || $Notify->event == AbstractNotify::EVENT_END
            ) {
                //кінець вхідного дзвінка на внутрішній номер АТС
                $NotifyValues = $Notify->toArray();
                array_walk($NotifyValues, function (&$v, $k) {
                    if (($k == 'caller_id' || $k == 'destination') && !empty($v))
                        $v = preg_replace("/[^0-9]/", "", $v);
                });
                $CallEvent = (new CallsEvents)->fill(
                    array_filter(
                        $NotifyValues,
                        fn($key) => in_array($key, (new CallsEvents)->getFillable()),
                        ARRAY_FILTER_USE_KEY
                    ));
                $CallEvent->pbx_id = $this->pbxID;
                $CallEvent->save();

                if($Notify->event == AbstractNotify::EVENT_START){
                    logger_zadarma('EVENT_START', [
                        'model' => $CallEvent
                    ]);
                }

                RecordCreateService::handler($CallEvent, ['division_id' => $division_id]);

                if ($Notify->event == AbstractNotify::EVENT_END) {

                    logger_zadarma('EVENT_END', [
                        'model' => $CallEvent
                    ]);

                    CommunicationStatusService::updatePbxDataByCall($CallEvent, $Notify->event);

                    try {
                        broadcast(new ZadarmaCallEvent($CallEvent, $division_id));
                    } catch (Exception $e) {
                        Log::error($e);
                        report($e);
                    }
                }

                // создание лида, заготовка
                if (
                    $Notify->event == AbstractNotify::EVENT_END
                    && $Notify->disposition != 'answered'
                ) {
                    $this->unansweredCall($Notify->caller_id);
                }

            } elseif (
                $Notify->event == AbstractNotify::EVENT_OUT_START
                || $Notify->event == AbstractNotify::EVENT_OUT_END
                || $Notify->event == AbstractNotify::EVENT_ANSWER
            ) {

                logger_zadarma('EVENT', [
                    'event' => $Notify->event
                ]);

                //кінець вихідного дзвінка з АТС или ответ АТС
                $NotifyValues = $Notify->toArray();
                array_walk($NotifyValues, function (&$v, $k) {
                    if (($k == 'caller_id' || $k == 'destination' || $k == 'internal') && !empty($v))
                        $v = preg_replace("/[^0-9]/", "", $v);
                });
                if (strlen($NotifyValues['internal']) > 3 && strlen($NotifyValues['destination']) <= 4) {
                    $NotifyValues['internal'] = $Notify->destination;
                    $NotifyValues['destination'] = $Notify->internal;
                }

                $CallEvent = (new CallsEvents)->fill(
                    array_filter(
                        $NotifyValues,
                        fn($key) => in_array($key, (new CallsEvents)->getFillable()),
                        ARRAY_FILTER_USE_KEY
                    ));
                $CallEvent->pbx_id = $this->pbxID;
                $CallEvent->save();

                if($Notify->event == AbstractNotify::EVENT_ANSWER){

                    CommunicationStatusService::updatePbxDataByCall($CallEvent, $Notify->event);

                    logger_zadarma("EVENT_ANSWER", [
                        'CallEvent' => $CallEvent->toArray()
                    ]);
                }

                RecordCreateService::handler($CallEvent, ['division_id' => $division_id]);

                if ($Notify->event == AbstractNotify::EVENT_OUT_END && strlen(Phone::clearPhone($CallEvent->destination)) > 6) {

                    logger_zadarma("EVENT_OUT_END", [
                        'CallEvent' => $CallEvent->toArray()
                    ]);

                    CommunicationStatusService::updatePbxDataByCall($CallEvent, $Notify->event);

                    try {
                        broadcast(new ZadarmaCallEvent($CallEvent, $division_id));
                    } catch (Exception $e) {
                        Log::error($e);
                        report($e);
                    }

                }

            } elseif ($Notify->event == 'SMS') {
                // only inbound
                $SmsEvents = (new SmsEvents)->fill(
                    array_filter(
                        $Notify->toArray(),
                        fn($key) => in_array($key, (new SmsEvents)->getFillable()),
                        ARRAY_FILTER_USE_KEY
                    ));
                $SmsEvents->pbx_id = $this->pbxID;
                $SmsEvents->inbound = 1;
                $SmsEvents->save();

                RecordCreateService::handler($SmsEvents, ['division_id' => $division_id]);

            } elseif ($Notify->event == AbstractNotify::EVENT_RECORD) {
                $CallRecord = (new CallsRecords)->fill(
                    array_filter(
                        $Notify->toArray(),
                        fn($key) => in_array($key, (new CallsRecords)->getFillable()),
                        ARRAY_FILTER_USE_KEY
                    ));
                $CallRecord->pbx_id = $this->pbxID;
                $CallRecord->save();
            }
//                // запись разговора
//            else {
//                Storage::append('webhook.log', print_r($Notify, 1) . PHP_EOL . PHP_EOL);
//            }

        } catch (Exception $e) {
            Log::error('Zadarma Webhook: ' . $e->getMessage());
            report($e);
        }

        return response()->json(null);
    }


    private function initZadarmaApi($division_id)
    {
//        logger_zadarma('INIT ZADARMA API START', ['division_id' => $division_id]);

        if (!$this->Api) {
            $Division = Division::findOrFail($division_id);
            $apiKey = null;
            $apiSecret = null;
            if (!empty($Division->miscs['zadarma_pbx_id']))
                $this->pbxID = $Division->miscs['zadarma_pbx_id'];
            if (!empty($Division->miscs['zadarma_api_key']))
                $apiKey = $Division->miscs['zadarma_api_key'];
            if (!empty($Division->miscs['zadarma_api_secret']))
                $apiSecret = $Division->miscs['zadarma_api_secret'];
            $this->Api = (new PBXController())->getAPI($apiKey, $apiSecret);
        }

//        logger_zadarma('INIT ZADARMA API', ['api' => $this->Api]);

        return $this->Api;
    }

    public function unansweredCall($phoneNumber)
    {
        logger_zadarma('unansweredCall');

        return null;
        dump($phoneNumber);
        $PBX = new PBXController();
        try {
            // check US number
            if ($PBX->isValidPhoneNumber($phoneNumber)) {
                $national = preg_replace("/[^0-9]/", "", $PBX->getNationalPhoneNumber($phoneNumber));
                if ($LastOrder = Order::unClosed()->where('user_id', '>', 0)->whereHas('client.phones', function ($q) use ($national) {
                    return $q->where('value', $national);
                })->whereHas('manager', function (Builder $q) {
                    // not Fired Employee?
//                    return $q->whereHas('employee', function(Builder $q) {
//                        return $q->where('active', 1);
//                    })
                    return $q->where('active', 1);
                })->orderBy('id', 'DESC')->first()) {
                    // Task

                } else {
                    //echo 'create new Order';
                }


//                Order::
                //echo 'valid US number ' . $national;
            }
        } catch (Exception $e) {
            echo 'Exception: ' . $e->getMessage();
        }

        return response('test1', 200, ['Content-Type' => 'text/html']);

    }


}
