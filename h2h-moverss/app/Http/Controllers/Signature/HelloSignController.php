<?php

namespace App\Http\Controllers\Signature;

use App\Http\Controllers\Controller;
use Dropbox\Sign\Api\SignatureRequestApi;
use Dropbox\Sign\ApiException;
use Dropbox\Sign\Configuration;
use Dropbox\Sign\Model\SignatureRequestSendWithTemplateRequest;
use Illuminate\Http\Request;
use App\Models\{Order, Division, Client};
use Exception;

class HelloSignController extends Controller
{
    //
    public function signatureRequest(Order $Order)
    {
        try {
            $orderID = $Order->id;
            $Order->load([
                'client',
                'client.phones',
                'client.emails',
                'waypoints',
                'estimate'
            ]);


            $Origin = $Order->waypoints->first(function ($Waypoint) {
                return $Waypoint->type == 'pickup';
            });
            $Destination = $Order->waypoints->first(function ($Waypoint) {
                return $Waypoint->type == 'destination';
            });

            if (!$Origin) {
                throw new Exception("Order #{$orderID} hasn't waypoints with 'pickup' type");
            }
            if (!$Destination) {
                throw new Exception("Order #{$orderID} hasn't waypoints with 'destination' type");
            }
            if (!$Order->client || !$Order->client->phones) {
                throw new Exception("Customer hasn't phones!");
            }
            if (!$Order->client || !$Order->client->emails) {
                throw new Exception("Customer hasn't emails!");
            }
            if (!$Order->estimate || $Order->estimate->type != 'interstate') {
                throw new Exception("Order #{$orderID} estimate type isn't interstate");
            }

            $phones = $Order->client->phones->pluck('value')->map(function ($v) {
                return Client\Phone::getInternationalPhoneNumber($v, 'US');
            })->all();
            $CalculatedTotal = $Order->calculated->first(function ($v) use ($Order) {
                return $v->title == 'total' && $v->estimate_type == $Order->estimate->type;
            });
            $CalculatedTotalValue = preg_replace("/[\$,]/", '', $CalculatedTotal->value);

            $Division = Division::find($Order->division_id);
            $hellosign_api_key = !empty($Division->miscs['hellosign_api_key']) ? $Division->miscs['hellosign_api_key'] : '';
            if (empty($hellosign_api_key)) {
                throw new Exception("HelloSign API key not defined");
            }
            $hellosign_api_cc_email = !empty($Division->miscs['hellosign_api_cc_email']) ? $Division->miscs['hellosign_api_cc_email'] : '';
            if (empty($hellosign_api_cc_email)) {
                throw new Exception("HelloSign CC email not defined");
            }
            $hellosign_interstate_template_id = !empty($Division->miscs['hellosign_interstate_template_id']) ? $Division->miscs['hellosign_interstate_template_id'] : '';
            if (empty($hellosign_interstate_template_id)) {
                throw new Exception("HelloSign interstate template id not defined");
            }


//            $Order->client->emails->first()->pluck('value');

            $config = Configuration::getDefaultConfiguration();
            $config->setUsername($hellosign_api_key);
            $api = new SignatureRequestApi($config);
            $ClientFullname = $Order->client->ClientFullName();
            $ClientEmail = $Order->client->emails->first()->value;
//            $accountApi = new AccountApi($config);

            $request = SignatureRequestSendWithTemplateRequest::init([
                "template_ids" => [
                    $hellosign_interstate_template_id
//                    "31d088bbff2f098cbdc44c73bc3a60eecba27194"
                ],
                "subject" => "Your Moving Contract - Signature Required",
                "message" => "Dear " . $ClientFullname . '!' . PHP_EOL . PHP_EOL .
                    "Please see a contract for your upcoming move. The contract has to be reviewed and signed no later than 3 days before your move. Please let us know if you have any questions or concerns. We look forward to moving you!",
                "signers" => [
                    [
                        "role" => "Customer",
                        "name" => $ClientFullname,
                        "email_address" => $ClientEmail
                    ]
                ],
                "ccs" => [
                    [
                        "role" => "Accounting",
                        "email_address" => $hellosign_api_cc_email
                    ]
                ],
                "custom_fields" => [
                    [
                        "name" => "order_id",
                        "value" => strval($Order->id)
                    ],
                    [
                        "name" => "origin_name",
                        "value" => $ClientFullname
                    ],
                    [
                        "name" => "origin_address",
                        "value" => $Origin->address
                    ],
                    [
                        "name" => "origin_city_state_zip",
                        "value" => $Origin->city . ' / ' . $Origin->state . ' / ' . $Origin->zip
                    ],
                    [
                        "name" => "origin_phone",
                        "value" => implode(', ', $phones)
                    ],
                    [
                        "name" => "destination_name",
                        "value" => $ClientFullname
                    ],
                    [
                        "name" => "destination_address",
                        "value" => $Destination->address
                    ],
                    [
                        "name" => "destination_city_state_zip",
                        "value" => $Destination->city . ' / ' . $Destination->state . ' / ' . $Destination->zip
                    ],
                    [
                        "name" => "destination_phone",
                        "value" => implode(', ', $phones)
                    ],
                    [
                        "name" => "total_estimated",
                        "value" => strval($CalculatedTotalValue + 0)
                    ],
                    [
                        "name" => "total_minimum",
                        "value" => strval($CalculatedTotalValue + 0)
                    ],
                    [
                        "name" => "volume_weight",
                        "value" => ($Order->sizing_volume + 0) . " / " . ($Order->sizing_weight + 0)
                    ]
                ],
                "test_mode" => false,
                "signing_options" => [
                    "draw" => true,
                    "type" => true,
                    "upload" => true,
                    "phone" => false,
                    "default_type" => "draw"
                ],
            ]);


//            $result = $accountApi->accountGet(null, 'allymovers.com@gmail.com');
//            print_r($result);
            $result = $api->signatureRequestSendWithTemplate($request);
            $response = [
                'success' => true,
                'sentTo' => $ClientEmail
            ];
//            $result = $api->signatureRequestCreateEmbeddedWithTemplate($request);
//            dump($result);
        } catch (ApiException $e) {
            $error = $e->getResponseObject();
            $msg = "Dropbox Sign API Exception [" . $error->getError()['error_name'] . "]: " . $error->getError()['error_msg'];
            $response = [
                'success' => false,
                'msg' => $msg
            ];
//            echo "Exception when calling Dropbox Sign API: "
//                . $error->getMessage() . PHP_EOL;
        } catch (Exception $e) {
            $msg = "Request Exception: " . $e->getMessage();
            $response = [
                'success' => false,
                'msg' => $msg
            ];
        }

        return response()
            ->json($response);
    }
}
