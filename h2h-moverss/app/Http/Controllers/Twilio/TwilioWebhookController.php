<?php

namespace App\Http\Controllers\Twilio;

use App\Events\TwilioSmsEvent;
use App\Http\Controllers\Controller;
use App\Services\Communications\RecordCreateService;
use Illuminate\Http\Request;
use Twilio;
use Twilio\TwiML\MessagingResponse;
use Illuminate\Support\Facades\{Storage};
use App\Models\Twilio\{TwilioSmsStatus, TwilioSms};
use App\Http\Controllers\Zadarma\PBXController;

class TwilioWebhookController extends Controller
{

    public static function detectDivision($phone)
    {
        $phones2Division = [
            '12137847797' => 2,
            '17732368896' => 1
        ];
        if (array_key_exists($phone, $phones2Division))
            return $phones2Division[$phone];

        return null;
    }


    public function handleSms(Request $request)
    {
        $data = $request->toArray();
//        dd(self::detectDivision(PBXController::clearPhone($data['To'])));

//        logger_twilio('WEBHOOK - handler sms', [
//            'data' => $data
//        ]);

        if (!empty($data['MessageSid']) && !TwilioSms::where('sid')->count()) {
            $twilioSms = TwilioSms::create([
                'sid' => $data['MessageSid'],
                'division' => self::detectDivision(PBXController::clearPhone($data['To'])),
                'direction' => 'inbound',
                'from' => $data['From'],
                'to' => $data['To'],
                'body' => $data['Body'],
                'misc' => json_encode($data)
            ]);

            RecordCreateService::handler($twilioSms);

            try {
                broadcast(new TwilioSmsEvent($twilioSms, $twilioSms->division));
            } catch (Exception $e) {
                Log::error($e);
                report($e);
            }
        }
        $this->reponseToTwilio(new MessagingResponse());
    }

    public function handleSmsStatus(Request $request)
    {
        $data = $request->toArray();
        TwilioSmsStatus::create(['sid' => $data['SmsSid'], 'status' => $data['SmsStatus'], 'from' => $data['From'], 'to' => $data['To']]);
        //Storage::append('twilio.log', 'SmsStatus'.PHP_EOL.print_r($request->toArray(), 1) . PHP_EOL . PHP_EOL);
        $this->reponseToTwilio(new MessagingResponse());
    }


    private function reponseToTwilio($response)
    {
        app('debugbar')->disable();
        return response((string)$response);
    }
}
