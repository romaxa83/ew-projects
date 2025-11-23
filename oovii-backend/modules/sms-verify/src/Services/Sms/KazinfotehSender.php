<?php

namespace WezomCms\SmsVerify\Services\Sms;

use Illuminate\Support\Facades\Http;

// @see http://docs.kazinfoteh.kz/protocols/http/outbox/
class KazinfotehSender implements SmsSender
{
    private string $url;
    private string $login;
    private string $password;

    public function __construct(string $url, string $login, string $password)
    {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
    }

    public function send(string $number, string $text): void
    {
        $query = http_build_query([
            'action' => 'sendmessage',
            'username' => $this->login,
            'password' => $this->password,
            'recipient' => $this->prettyPhone($number),
            'messagetype' => 'SMS:TEXT',
            'originator' => 'INFO_KAZ',
            'messagedata' => $text
        ]);

        try {
            $response = Http::get("{$this->url}?{$query}");
            logger("SMS_SENDER_SEND [{$response}]");

            $res = $this->parseXML($response->body());
            if(isset($res['action']) && $res['action'] == 'error'){
                $msg = $res['data']['errormessage'] ?? null;
                logger("SMS_SENDER_FAIL - [{$msg}]");
                throw new \Exception("Fail sms-sender, error [{$msg}]");
            }

            logger("SMS_SENDER_RESPONSE [{$number}] - [{$response->body()}]");
        } catch (\Throwable $e){
            throw new \Exception($e->getMessage());
        }
    }

    private function prettyPhone($value)
    {
        return str_replace(' ', '',
                str_replace('-', '',
                    str_replace('+', '',
                        str_replace(')','',
                            str_replace('(', '', $value)
                        )
                    )
                )
        );
    }

    private function parseXML(string $data)
    {
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        return json_decode($json,TRUE);
    }
}
