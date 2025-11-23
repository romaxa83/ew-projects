<?php

namespace App\Http\Controllers\Zadarma;

use App\Events\TwilioSmsEvent;
use App\Http\Controllers\CommunicationsController;
use App\Http\Controllers\Controller;
use App\Services\Communications\RecordCreateService;
use App\Traits\ResponseFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Models\{Attachment,
    Division,
    Employee,
    Employee\PbxData,
    Order,
    Client,
    Client\Phone,
    Twilio\TwilioSms,
    Zadarma\CallsEvents,
    Zadarma\SmsEvents};
use App\User;
use Illuminate\Support\Facades\{Auth, DB, Http, Cache, Storage, Log};
use Illuminate\Http\{Request, JsonResponse, UploadedFile};
use Exception, DateTimeZone;
use Zadarma_API\{Api, ApiException};
use libphonenumber\{PhoneNumberUtil, NumberParseException, PhoneNumberFormat, ValidationResult};
use Twilio;

//use Aloha\Twilio\Twilio as TwilioConnection;
use App\Http\Controllers\Twilio\TwilioWebhookController;

class PBXController extends Controller
{

    use ResponseFormatter;

//    protected $zararma_api_url = 'https://api.zadarma.com';
//    protected $ZadarmaAPI = null;
    protected $PBXTimezone = 'America/Los_Angeles'; // притянуть из Division. For reports
    protected $countryCode = 'US'; //US for phonenumber validation and get full number
    private $ZadarmaApiKey = null;
    private $ZadarmaApiSecret = null;
    private $ZadarmaPBXid = null;
    private $ZadarmaPBXcallerID = '+12137840373';


    private function initVariables()
    {
        $divisionMiscs = session()->get('division.miscs');
        if (!empty($divisionMiscs['tz'])) {
            $this->PBXTimezone = $divisionMiscs['tz'];
        }
        if (!empty($divisionMiscs['zadarma_pbx_id'])) {
            $this->ZadarmaPBXid = $divisionMiscs['zadarma_pbx_id'];
        }
        if (!empty($divisionMiscs['zadarma_api_key'])) {
            $this->ZadarmaApiKey = $divisionMiscs['zadarma_api_key'];
        }
        if (!empty($divisionMiscs['zadarma_api_secret'])) {
            $this->ZadarmaApiSecret = $divisionMiscs['zadarma_api_secret'];
        }
        if (!empty($divisionMiscs['zadarma_pbx_caller_id'])) {
            $this->ZadarmaPBXcallerID = $divisionMiscs['zadarma_pbx_caller_id'];
        }
        $locationData = (new DateTimeZone($this->PBXTimezone))->getLocation();
        $this->countryCode = $locationData['country_code'];
        return $this;
    }


    public function hasZadarma()
    {
        $this->initVariables();
        return $this->ZadarmaApiKey && $this->ZadarmaApiSecret && $this->ZadarmaPBXid;
    }


    public function isValidPhoneNumber($phonenumber)
    {
        $PhoneUtil = PhoneNumberUtil::getInstance();
        $NumberProto = $PhoneUtil->parse($phonenumber, $this->countryCode);
        if (!$PhoneUtil->isValidNumber($NumberProto)) {
            $errorCode = $PhoneUtil->isPossibleNumberWithReason($NumberProto);
            $VR = new \ReflectionClass(ValidationResult::class);
            $constants = array_flip($VR->getConstants());
            throw new \Exception('PhoneNumber "' . $phonenumber . '" not valid. Reason: "' . $constants[$errorCode] . '"');
            return null;
        }
        return true;
    }


    private function getInternationalPhoneNumber($phonenumber)
    {
        $PhoneUtil = PhoneNumberUtil::getInstance();
        $NumberProto = $PhoneUtil->parse($phonenumber, $this->countryCode);
        return $PhoneUtil->format($NumberProto, PhoneNumberFormat::INTERNATIONAL);
    }


    public function ajaxZadarmaRecord(Request $request)
    {
        $response = ['success' => false];
        try {
            $this->initVariables();
            $validated = $request->validate([
                'callID' => 'required',
                'pbx_callID' => 'required',
//                'orderID' => 'required|integer|exists:orders,id',
            ]);
//            dd(session('division'));
//            $Order = Oer::find($validated['orderID']);
            $Division = Division::find(session('division')['id']);
            $Api = $this->getAPI($Division->miscs['zadarma_api_key'], $Division->miscs['zadarma_api_secret']);
            $records = $Api->getPbxRecord($validated['callID'], $validated['pbx_callID']);
//            dd($record);
            $response['success'] = true;
            $response['links'] = $records->links;
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
            report($e);
        }

        return response()
            ->json($response);
    }


    public function ajaxOrderCalls(Request $request)
    {
        $response = ['success' => false];
        try {
            //$this->initVariables();
//            $records = [];
            $validated = $request->validate([
                'orderId' => 'required|integer|exists:orders,id',
            ]);
            $Order = Order::with('client.phones')->findOrFail($validated['orderId']);
            if ($Order->client_id && $Order->client->phones->count() > 0) {
                /**
                 * @var $Phone Phone
                 */
                $records = $this->getZadarmaPhoneLog($Order, $Order->client->phones->pluck('value')->toArray());

            }
            $response['success'] = true;
            $response['records'] = $records;
        } catch (Exception $e) {
            $response['msg'] = $e->getMessage();
        }

        return response()
            ->json($response);

    }

    /**
     * @param Order $Order
     * @param array $phones
     * @param $records
     * @param $params ['limit', 'dateFrom', 'dateTo']
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getZadarmaPhoneLog(Order $Order, array $phones, $params = [])
    {
        $this->initVariables();
        if (empty($params['dateFrom']))
            $params['dateFrom'] = $Order->created_at->modify('-2 days midnight')->setTimezone(new DateTimeZone($this->PBXTimezone));
        if (empty($params['dateTo']))
            $params['dateTo'] = $Order->updated_at->modify('+3 month midnight')->setTimezone(new DateTimeZone($this->PBXTimezone));

//        // Заказ со статусом закрыто
//        if (in_array($Order->status_id, [9, 10, 12, 13, 14, 19])) {
//            $params['dateTo'] = $Order->updated_at->modify('+2 days midnight')
//                ->setTimezone(new DateTimeZone($this->PBXTimezone));
//        }

        if (empty($params['limit']))
            $params['limit'] = 1000;

        $params['dateTo']->setTimezone(new DateTimeZone($this->PBXTimezone));

        // выбираем старт разговора
        return CallsEvents::with([
            'internalEmployee:id,name,l_name',
            'internalPbxData:employee_id,pbx_ext,pbx_id',
            'internalPbxData.employee:id,name,l_name'
        ])
            ->where('pbx_id', $this->ZadarmaPBXid)
            ->where(function (Builder $q) use ($phones) {
                $q->where(function (Builder $query) use ($phones) {
                    return $query->where('event', '=', 'NOTIFY_END')
                        ->where(function ($query) use ($phones) {
                            if (!empty($phones))
                                foreach ($phones as $phone)
                                    $query->orWhere('caller_id', 'LIKE', '%' . $phone);
                        });

                })->orWhere(function ($query) use ($phones) {
                    return $query->where('event', '=', 'NOTIFY_OUT_END')
                        ->where(function ($query) use ($phones) {
                            if (!empty($phones))
                                foreach ($phones as $phone)
                                    $query->orWhere('destination', 'LIKE', '%' . $phone);
                        });
                });
            })
            ->whereBetween('call_start',
                [$params['dateFrom']->format('Y-m-d H:i:s'), $params['dateTo']->format('Y-m-d H:i:s')])
            ->orderBy('call_start', 'DESC')
            ->limit($params['limit'])
            ->get();

        // выбираем окончание разговора
    }

    public function getPBXid()
    {
        if (!$this->ZadarmaPBXid)
            throw new Exception('Not found variable ZADARMA_PBX!');
        return $this->ZadarmaPBXid;
    }

    public function getAPI($ZadarmaApiKey = null, $ZadarmaApiSecret = null)
    {
        $this->initVariables();
        if (empty($ZadarmaApiKey) && !empty($this->ZadarmaApiKey))
            $ZadarmaApiKey = $this->ZadarmaApiKey;
        elseif (empty($ZadarmaApiSecret) && empty($this->ZadarmaApiSecret))
            throw new Exception('Check ZADARMA_API_KEY');

        if (empty($ZadarmaApiSecret) && !empty($this->ZadarmaApiSecret)) {
            $ZadarmaApiSecret = $this->ZadarmaApiSecret;
        } elseif (empty($ZadarmaApiSecret) && empty($this->ZadarmaApiSecret))
            throw new Exception('Check ZADARMA_API_SECRET');

        return new Api($ZadarmaApiKey, $ZadarmaApiSecret);
    }

    /**
     * @return \Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed
     * @throws Exception
     */
    public function getUserPBXExtension()
    {
        $User = User::find(Auth::id());
        if ($User && $PbxData = $this->getUserPBXData()) {
            return $PbxData->pbx_ext;
        }
        return null;
    }

    public function getUserPBXData()
    {
        $User = User::find(Auth::id());
        if ($User && $Employer = $User->employee()->first()) {
            $this->initVariables();
            $PbxData = PbxData::where('pbx_id', $this->ZadarmaPBXid)
                ->where('employee_id', $Employer->id)->first();
            if (!$PbxData)
                return null;
//                throw new Exception('User have no valid PBX extension!');
            return $PbxData;
        } else {
            throw new Exception('User have no linked employee!');
        }
        return null;
    }


    private function getWebRTCKey()
    {
        $this->initVariables();
        $extension = $this->getUserPBXExtension();
        $sip = $this->getPBXid() . '-' . $extension;
        if (!Cache::has('webRtcKey-' . $sip)) {
            $Api = $this->getAPI();
            Cache::put('webRtcKey-' . $sip, $Api->getWebrtcKey($sip), 20 * 60 * 60);
        }
        // 20 * 60 * 60
//        return Cache::put('webRtcKey-' . $sip, '', 20 * 60 * 60);
        return Cache::get('webRtcKey-' . $sip);
    }

    public function initPBX(): JsonResponse
    {
        try {
            $this->initVariables();
            $data = [];
            $User = User::find(Auth::id());
            if ($User && $Employer = $User->employee()->first()) {
                if (empty($Employer->pbx_show_webrtc))
                    throw new Exception('PBX WebRTC disabled');
            }
            $extension = $this->getUserPBXExtension();
            // api
            $sip = $this->getPBXid() . '-' . $extension;
            $data['webrtcKey'] = $this->getWebRTCKey();
            $data['sip'] = $sip;

        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'data' => $data
            ]);
    }

    public static function clearPhone($phone, $keepPlus = false)
    {
        if ($keepPlus)
            return preg_replace("/[^0-9\+]/", "", $phone);
        return preg_replace("/[^0-9]/", "", $phone);
    }

    public function sendSMSOld(Request $request)
    {
        try {
            $this->initVariables();
            $validated = $request->validate([
                'phone' => 'required',
                'text' => 'required|string',
                'attachments' => 'array',
                'attachments.*' => 'nullable|file|max:'.(16 * 1024),
            ]);

            $internationalNumber = $this->getInternationalPhoneNumber($validated['phone']);
            $internationalNumber = self::clearPhone($internationalNumber, true);

            $division = $request->session()->get('division');
            if ($division['id'] == 2 || $division['id'] == 1) {
                if ($division['id'] == 2) {
                    $connection = 'california';
                } elseif ($division['id'] == 1) {
                    $connection = 'illinois';
                }

                $params = [
                    'messagingServiceSid' => config('app.env') === 'production'
                        ? config('twilio.twilio.messaging_sid')
                        : 'MGffbfc8b009bf6f89e6c41251f770ae3f'
                ];

                $models = [];
                $urls = [];

                foreach ($validated['attachments'] as $file) {
                    /** @var $file UploadedFile */
                    $hash = hash_file('sha256', $file->path());
                    $folder = "attachments/twilio/";
                    $miscs = [
                        'patch' => $folder,
                        'size' => human_filesize($file->getSize()),
                        'name' => strip_tags($file->getClientOriginalName()),
                        'ext' => $file->getClientOriginalExtension(),
                    ];

                    Storage::disk('public')
                        ->putFileAs($folder, $file, $hash.'.'.$file->getClientOriginalExtension());

                    $model = new Attachment();
                    $model->user_id = Auth::id();
                    $model->hash = $hash;
                    $model->miscs = [
                        'file' => $miscs,
                    ];
                    $model->save();

                    $models[] = $model;
                    $urls[] = $model->getUrl();
                }

                $TwilioMessage = Twilio::from($connection)
                    ->message(self::clearPhone($internationalNumber, true), $validated['text'], $urls,
                        config('app.env') === 'production'
                            ? ['statusCallback' => route('webhook.twilio.sms.handleSmsStatus')] + $params
                            : $params
                    );

                $data = $TwilioMessage->toArray();

                $misc = ['numSegments', 'price', 'errorMessage', 'status', 'numMedia', 'errorCode', 'priceUnit'];
                $TwilioSMS = TwilioSms::create([
                    'sid' => $data['sid'],
                    'division' => TwilioWebhookController::detectDivision(PBXController::clearPhone($data['from'])),
                    'direction' => $data['direction'],
                    'from' => $data['from'],
                    'to' => $data['to'],
                    'body' => $data['body'],
                    'misc' => collect($data)->filter(function ($value, $key) use ($misc) {
                        if (in_array($key, $misc))
                            return true;
                        return false;
                    })->toJson()
                ]);

                RecordCreateService::handler($TwilioSMS);

                foreach ($models as $model) {
                    $model->update([
                        'entity_id' => $TwilioSMS->id,
                        'entity_type' => TwilioSms::MORPH_NAME,
                    ]);
                }

                try {
                    broadcast(new TwilioSmsEvent($TwilioSMS, $division['id']));
                } catch (Exception $e) {
                    Log::error($e);
                    report($e);
                }
            }
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'titledError' => [
                        'title' => $e->getMessage()
                    ]

                ]);
        }
        return response()
            ->json([
                'success' => true,
                'sms' => $this->getCommunicationPanelFormat($TwilioSMS),
//                'data' => $sended
            ]);
    }

    /**
     * test @see \Tests\Feature\Zadarma\Pbx\SendSmsTest
     */
    public function sendSMS(Request $request)
    {
        \Log::info('Send SMS Start [PBXController@sendSMS]', [
            'data' => $request->all(),
            'divisionId' => $request->session()->get('division.id')
        ]);

        try {
            $this->initVariables();
            $validated = $request->validate([
                'phone' => 'required',
                'text' => 'required|string',
                'attachments' => 'array',
                'attachments.*' => 'nullable|file|max:'.(16 * 1024),
            ]);

            $division = $request->session()->get('division');
            if ($division['id'] == 2 || $division['id'] == 1) {
                if ($division['id'] == 2) {
                    $connection = 'california';
                } elseif ($division['id'] == 1) {
                    $connection = 'illinois';
                }

                $models = [];
                $urls = [];

                foreach ($validated['attachments'] ?? [] as $file) {
                    /** @var $file UploadedFile */
                    $hash = hash_file('sha256', $file->path());
                    $folder = "attachments/twilio/";
                    $miscs = [
                        'patch' => $folder,
                        'size' => human_filesize($file->getSize()),
                        'name' => strip_tags($file->getClientOriginalName()),
                        'ext' => $file->getClientOriginalExtension(),
                    ];

                    Storage::disk('public')
                        ->putFileAs($folder, $file, $hash.'.'.$file->getClientOriginalExtension());

                    $model = new Attachment();
                    $model->user_id = Auth::id();
                    $model->hash = $hash;
                    $model->miscs = [
                        'file' => $miscs,
                    ];
                    $model->save();

                    $models[] = $model;
                    $urls[] = $model->getUrl();
                }

                $data = $this->sendSmsAsTwilio(
                    $connection,
                    $validated['phone'],
                    $validated['text'],
                    $urls
                );

                \Log::info('Send SMS success', [
                    'data' => $data,
                ]);

                $misc = ['numSegments', 'price', 'errorMessage', 'status', 'numMedia', 'errorCode', 'priceUnit'];
                $TwilioSMS = TwilioSms::create([
                    'sid' => $data['sid'],
                    'division' => TwilioWebhookController::detectDivision(PBXController::clearPhone($data['from'])),
                    'direction' => $data['direction'],
                    'from' => $data['from'],
                    'to' => $data['to'],
                    'body' => $data['body'],
                    'misc' => collect($data)->filter(function ($value, $key) use ($misc) {
                        if (in_array($key, $misc))
                            return true;
                        return false;
                    })->toJson()
                ]);

                $rec = RecordCreateService::handler($TwilioSMS);

                \Log::info('Send SMS create record', [
                    'twilioSmsModelId' => $TwilioSMS->id,
                    'comRecordId' => $rec?->id,
                ]);

                foreach ($models as $model) {
                    $model->update([
                        'entity_id' => $TwilioSMS->id,
                        'entity_type' => TwilioSms::MORPH_NAME,
                    ]);
                }

                try {
                    broadcast(new TwilioSmsEvent($TwilioSMS, $division['id']));
                } catch (Exception $e) {
                    Log::error('Send SMS FAIL broadcast', [$e]);
                    report($e);
                }
            }
        } catch (Exception $e) {
            Log::error('Send SMS FAIL', [$e]);
            return response()
                ->json([
                    'success' => false,
                    'titledError' => [
                        'title' => $e->getMessage()
                    ]

                ]);
        }

        \Log::info('Send SMS FINISH', [
            'data' => $request->all(),
            'divisionId' => $request->session()->get('division.id')
        ]);

        return response()
            ->json([
                'success' => true,
                'sms' => $this->getCommunicationPanelFormat($TwilioSMS),
//                'data' => $sended
            ]);
    }

    public function sendSmsAsTwilio(
        string $connection,
        string $phone,
        string $text,
        array $urls = [],
    ): array
    {
        $internationalNumber = $this->getInternationalPhoneNumber($phone);
        $internationalNumber = self::clearPhone($internationalNumber, true);

        $params = [
            'messagingServiceSid' => config('app.env') === 'production'
                ? config('twilio.twilio.messaging_sid')
                : 'MGffbfc8b009bf6f89e6c41251f770ae3f'
        ];

        $twilioMessage = Twilio::from($connection)
            ->message(self::clearPhone($internationalNumber, true), $text, $urls,
                config('app.env') === 'production'
                    ? ['statusCallback' => route('webhook.twilio.sms.handleSmsStatus')] + $params
                    : $params
            );

        return $twilioMessage->toArray();
    }

    public function isAllowedCallWidget()
    {
        if (!$this->hasZadarma())
            return false;
        // allow admins to use widget
//        if (Auth::user()->isAdmin())
//            return true;
        if (!empty($this->getEmployeePbxExtention())) {
            return true;
        }
        return false;
    }


    public function getEmployeePbxExtention($employeeUserID = null)
    {
        if (!$employeeUserID)
            $employeeUserID = Auth::id();
        if ($employeeUserID) {
            $PbxData = PbxData::where('pbx_id', $this->ZadarmaPBXid)->whereHas('employee', function ($q) use ($employeeUserID) {
                $q->where('auth_user_id', $employeeUserID);
            })->first();

            if ($PbxData)
                return $PbxData->pbx_ext;
        }
        return null;
    }


    public function getActiveCalls()
    {
        $records = collect([]);
        try {
            $this->initVariables();
            // current user pbx_ext
//            $employee = Employee::where('auth_user_id', Auth::id())->get()->firstOrFail();
            $pbx_ext = $this->getEmployeePbxExtention();

            //dd($employee->toArray());
            $answeredCallIds = [];
            $callEventsAnswered = CallsEvents::where('pbx_id', $this->getPBXid())
                ->where('call_start', '>', Carbon::now($this->PBXTimezone)->startOfDay())
                ->where('event', 'NOTIFY_ANSWER')
                ->whereNotIn('pbx_call_id', function ($query) {
                    $query->select('pbx_call_id')->from((new CallsEvents())->getTable())->whereIn('event', ['NOTIFY_OUT_END', 'NOTIFY_END']);
                })
                ->get();
            // work with Answered
            if ($callEventsAnswered->isNotEmpty()) {
                foreach ($callEventsAnswered as $callEvent) {
                    $answeredCallIds[] = $callEvent->pbx_call_id;
                    if ($callEvent->internal == $pbx_ext) {
                        $records->push($this->getCommunicationPanelFormat($callEvent, $this->PBXTimezone));
                    }
                }
            }
            // find unanswered
            $callEventsActive = CallsEvents::where('pbx_id', $this->getPBXid())
                ->where('call_start', '>', Carbon::now($this->PBXTimezone)->startOfDay())
                ->whereIn('event', ['NOTIFY_START', 'NOTIFY_OUT_START'])
                ->where(function (Builder $query) use ($answeredCallIds) {
                    $query->whereNotIn('pbx_call_id', function ($query) {
                        $query->select('pbx_call_id')->from((new CallsEvents())->getTable())->whereIn('event', ['NOTIFY_OUT_END', 'NOTIFY_END']);
                    });
                    if (!empty($answeredCallIds))
                        $query->whereNotIn('pbx_call_id', $answeredCallIds);
                })->get();
            if ($callEventsActive->isNotEmpty()) {
                foreach ($callEventsActive as $callEvent) {
                    if ($callEvent->event == 'NOTIFY_OUT_START' && $callEvent->internal != $pbx_ext)
                        continue;
                    $records->push($this->getCommunicationPanelFormat($callEvent, $this->PBXTimezone));
                }
            }
            if ($records->isNotEmpty()) {
                $records = $records->map(CommunicationsController::class . '::mapRecord')
                    ->sortByDesc(function ($obj) {
                        return $obj->datetime;
                    });
            }

        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'error' => $e->getMessage()

                ]);
        }

        return response()
            ->json([
                'success' => true,
                'records' => array_values($records->toArray()),
                'settings' => [
                    'divisionID' => session('division')['id'],
                    'internal' => $pbx_ext ? +$pbx_ext : null
                ]
            ]);
    }


    public function callback(Request $request): JsonResponse
    {
        try {
            $this->initVariables();
            $validated = $request->validate([
                'phone' => 'required',
                'provider' => 'in:zadarma,twilio',

            ]);
            $provider = !empty($validated['provider']) ? $validated['provider'] : 'zadarma';
            $extension = $this->getUserPBXExtension();
            $Api = $this->getAPI();
//            $this->getInternationalPhoneNumber($validated['phone']);
            $callTo = self::clearPhone($validated['phone']);
            if (strlen($callTo) == 10 && $provider == 'zadarma') {
//                if($this->ZadarmaPBXid == '373685')
//                    $callTo = '8881' . $callTo;
//                else
                $callTo = '+1' . $callTo;

            }
            if ($provider == 'twilio') {
                if (strlen($callTo) == 10) {
                    $callTo = '1' . $callTo;
                }
                if ($this->ZadarmaPBXid == '373685') {
                    $callTo = '0001' . $callTo;
                }
                if ($this->ZadarmaPBXid == '339617') {
                    $callTo = '0002' . $callTo;
                }

            }
//            $Api->requestCallback($extension, $callTo, $extension);
            $Api->request('request/callback', ['from' => $extension, 'to' => $callTo, 'sip' => $extension, 'predicted' => null]);
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'exception' => $e,
                    'titledError' => [
                        'title' => $e->getMessage()
                    ]

                ]);
        }

        return response()
            ->json([
                'success' => true,
                'data' => []
            ]);

    }


}
