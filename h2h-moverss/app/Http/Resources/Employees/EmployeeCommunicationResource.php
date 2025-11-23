<?php

namespace App\Http\Resources\Employees;

use App\Http\Resources\Users\UserSimpleForEmployeeResource;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Ringostat\EventBeforeCall;
use App\Models\Zadarma\CallsEvents;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Employee
 */
class EmployeeCommunicationResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'first_name' => $this->name,
            'last_name' => $this->l_name,
            'is_online' => $this->isOnline(),
            'is_online_provider' => $this->isOnlineProvider(),
            'user' => UserSimpleForEmployeeResource::make($this->user),
            'call' => [],
        ];

        if($this->ringostat_call_rec_id){
            /** @var $ringoCall EventBeforeCall */
            $ringoCall = EventBeforeCall::find($this->ringostat_call_rec_id);

            $data['call'] = [
                'provider' => 'ringostat',
                'type' => $ringoCall->call_type,
                'number' => $ringoCall->call_type == 'out'
                    ? $ringoCall->destination
                    : $ringoCall->callers_number,
                'start_at' => $ringoCall->created_at->timestamp,
                'client_id' => null,
                'client_name' => null,
            ];
            if($ringoCall->client_id){
                $client = Client::find($ringoCall->client_id);
                $data['call']['client_id'] = $client->id;
                $data['call']['client_name'] = $client->full_name;
            }
        }
        if($this->zadarma_call_rec_id){
            /** @var $zadarmaCall CallsEvents */
            $zadarmaCall = CallsEvents::find($this->zadarma_call_rec_id);

            $data['call'] = [
                'provider' => 'zadarma',
                'type' => current(explode('_', $zadarmaCall->pbx_call_id)),
                'number' => $zadarmaCall->destination,
                'start_at' => $zadarmaCall->created_at->timestamp,
                'client_id' => null,
                'client_name' => null,
            ];
            if($zadarmaCall->client_id){
                $client = Client::find($zadarmaCall->client_id);
                $data['call']['client_id'] = $client->id;
                $data['call']['client_name'] = $client->full_name;
            }
        }

        return $data;
    }
}
