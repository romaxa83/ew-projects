<?php

namespace Tests\Fake\Sms;

class ClickSendFake
{
    public static function success(): string
    {
        return <<<JSON
{
    "http_code": 200,
    "response_code": "SUCCESS",
    "response_msg": "Messages queued for delivery.",
    "data": {
        "total_price": 0.117,
        "total_count": 1,
        "queued_count": 1,
        "messages": [
            {
                "direction": "out",
                "date":1641922847,
                "to": "+380502762716",
                "body": "code: 123",
                "from": "ClickSend",
                "schedule":0,
                "message_id": "91E32E0E-36BB-48A3-8701-3BA22AF95DBB",
                "message_parts": 1,
                "message_price": "0.1170",
                "from_email": null,
                "list_id": null,
                "custom_string": "",
                "contact_id": null,
                "user_id":296907,
                "subaccount_id": 337033,
                "country": "UA",
                "carrier": "Vodafone",
                "status": "SUCCESS"
            }
        ],
        "_currency": {
            "currency_name_short": "USD",
            "currency_prefix_d": "$",
            "currency_prefix_c": "\u00a2",
            "currency_name_long": "US Dollars"
        }
    }
}
JSON;
    }

    public static function noMoney(): string
    {
        return <<<JSON
{
   "http_code":200,
   "response_code":"SUCCESS",
   "response_msg":"Messages queued for delivery.",
   "data":{
      "total_price":0.1793,
      "total_count":1,
      "queued_count":0,
      "messages":[
         {
            "to":"+380502762716",
            "body":"code: 123",
            "from":"ClickSend",
            "schedule":0,
            "message_id":"C613471B-91AF-4E5A-94A8-5F9D2F38E7CC",
            "message_parts":1,
            "message_price":"0.1793",
            "custom_string":"",
            "country":"UA",
            "carrier":"Vodafone",
            "status":"INSUFFICIENT_CREDIT"
         }
      ],
      "_currency":{
         "currency_name_short":"AUD",
         "currency_prefix_d":"$",
         "currency_prefix_c":"c",
         "currency_name_long":"Australian Dollars"
      }
   }
}
JSON;
    }
}
