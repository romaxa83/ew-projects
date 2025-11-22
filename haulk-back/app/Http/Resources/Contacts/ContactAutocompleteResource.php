<?php

namespace App\Http\Resources\Contacts;

use App\Models\Contacts\Contact;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactAutocompleteResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * 
     * @OA\Schema(
     *    schema="ContactAutocompleteResource",
     *    @OA\Property(
     *        property="data",
     *        description="Contacts paginated list",
     *        type="array",
     *        @OA\Items(ref="#/components/schemas/ContactResource")
     *    ),
     * )
     * 
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'full_name' => $this->full_name,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state ? [
                'id' => $this->state->id,
                'name' => $this->state->name,
                'short' => $this->state->state_short_name,
            ] : null,
            'state_id' => $this->state_id,
            'zip' => $this->zip,
            'phone' => $this->phone,
            'phone_extension' => $this->phone_extension,
            'phone_name' => $this->phone_name,
            'phones' => $this->phones,
            'email' => $this->email,
            'fax' => $this->fax,
            'comment' => $this->comment,
            'comment_date' => $this->comment_date,
            'type' => $this->type_id ? [
                'id' => (int) $this->type_id,
                'title' => Contact::CONTACT_TYPES[$this->type_id],
            ] : null,
            'type_id' => $this->type_id ? (int) $this->type_id : null,
            'timezone' => $this->timezone,
            'working_hours' => $this->working_hours,
        ];
    }
}
