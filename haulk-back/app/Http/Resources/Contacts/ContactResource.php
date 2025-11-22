<?php

namespace App\Http\Resources\Contacts;

use App\Models\Contacts\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="ContactResourceRaw",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                required={"full_name"},
     *                    @OA\Property(property="full_name", type="string", description="Contact name"),
     *                    @OA\Property(property="address", type="string", description="Contact address"),
     *                    @OA\Property(property="city", type="string", description="Contact city"),
     *                        @OA\Property(property="state", type="object", description="Contact state", allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", description="State id"),
     *                          @OA\Property(property="name", type="string", description="State full name"),
     *                          @OA\Property(property="short", type="string", description="State short name"),
     *                      )
     *                  }),
     *                        @OA\Property(property="state_id", type="integer", description="Contact state id"),
     *                    @OA\Property(property="zip", type="string", description="Contact zip"),
     *                    @OA\Property(property="phone", type="string", description="Contact phone"),
     *                    @OA\Property(property="phone_extension", type="string", description="Contact phone extension"),
     *                    @OA\Property(property="phone_name", type="string", description="Contact person name"),
     *                    @OA\Property(property="phones", type="array", description="Contact phones", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="name", type="string", description="Contact person name"),
     *                          @OA\Property(property="number", type="string", description="Phone number"),
     *                          @OA\Property(property="notes", type="string", description="Phone number notes"),
     *                          @OA\Property(property="extension", type="string", description="Phone number extension"),
     *                      )
     *                  }
     *              ),),
     *                    @OA\Property(property="email", type="string", description="Contact email"),
     *                    @OA\Property(property="fax", type="string", description="Contact fax"),
     *                    @OA\Property(property="comment", type="string", description="Contact comment"),
     *                    @OA\Property(property="comment_date", type="string", description="Contact comment date"),
     *                    @OA\Property(property="type", type="object", description="Contact type", allOf={
     *                        @OA\Schema(
     *                            required={"id", "title"},
     *                                @OA\Property(property="id", type="integer", description="Contact type id"),
     *                                @OA\Property(property="title", type="string", description="Contact type name"),
     *                            )
     *                        }
     *                    ),
     *                    @OA\Property(property="type_id", type="integer", description="Contact type id"),
     *                    @OA\Property(property="timezone", type="string", description="Contact timezone"),
     *                    @OA\Property(property="working_hours", type="object", description="Contact working hours"),
     *                )
     *        }
     * )
     *
     * @OA\Schema(
     *    schema="ContactResource",
     *    type="object",
     *        @OA\Property(
     *            property="data",
     *            type="object",
     *            description="Contact data",
     *            allOf={
     *                @OA\Schema(
     *                    required={"full_name"},
     *                        @OA\Property(property="full_name", type="string", description="Contact name"),
     *                        @OA\Property(property="address", type="string", description="Contact address"),
     *                        @OA\Property(property="city", type="string", description="Contact city"),
     *                        @OA\Property(property="state", type="object", description="Contact state", allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", description="State id"),
     *                          @OA\Property(property="name", type="string", description="State full name"),
     *                          @OA\Property(property="short", type="string", description="State short name"),
     *                      )
     *                  }),
     *                        @OA\Property(property="state_id", type="integer", description="Contact state id"),
     *                        @OA\Property(property="zip", type="string", description="Contact zip"),
     *                        @OA\Property(property="phone", type="string", description="Contact phone"),
     *                        @OA\Property(property="phone_extension", type="string", description="Contact phone extension"),
     *                        @OA\Property(property="phone_name", type="string", description="Contact person name"),
     *                        @OA\Property(property="phones", type="array", description="Contact phones", @OA\Items(
     *                  type="object",
     *                  allOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="name", type="string", description="Contact person name"),
     *                          @OA\Property(property="number", type="string", description="Phone number"),
     *                          @OA\Property(property="notes", type="string", description="Phone number notes"),
     *                          @OA\Property(property="extension", type="string", description="Phone number extension"),
     *                      )
     *                  }
     *              ),),
     *                        @OA\Property(property="email", type="string", description="Contact email"),
     *                        @OA\Property(property="fax", type="string", description="Contact fax"),
     *                        @OA\Property(property="comment", type="string", description="Contact comment"),
     *                        @OA\Property(property="comment_date", type="string", description="Contact comment date"),
     *                        @OA\Property(property="type", type="object", description="Contact type", allOf={
     *                        @OA\Schema(
     *                            required={"id", "title"},
     *                                @OA\Property(property="id", type="integer", description="Contact type id"),
     *                                @OA\Property(property="title", type="string", description="Contact type name"),
     *                            )
     *                        }
     *                    ),
     *                    @OA\Property(property="type_id", type="integer", description="Contact type id"),
     *                    @OA\Property(property="timezone", type="string", description="Contact timezone"),
     *                        @OA\Property(property="working_hours", type="object", description="Contact working hours"),
     *                )
     *            }
     *        ),
     * )
     *
     */
    public function toArray($request): array
    {
        /** @var Contact $contact */
        $contact = $this;

        if ($request->header('Admin-Panel') === 'true') {
            $city = $contact->city;
        } else {
            $cityArr = explode(',', $contact->city);
            $city = $cityArr[0];
        }

        $state = $contact->getState();

        return [
            'id' => $contact->id,
            'user_id' => $contact->user_id,
            'full_name' => $contact->full_name,
            'address' => $contact->address,
            'city' => $city,
            'state' => $state ? [
                'id' => $state->id,
                'name' => $state->name,
                'short' => $state->state_short_name,
            ] : null,
            'state_id' => $contact->state_id,
            'zip' => $contact->zip,
            'phone' => $contact->phone,
            'phone_extension' => $contact->phone_extension,
            'phone_name' => $contact->phone_name,
            'phones' => $contact->phones,
            'email' => $contact->email,
            'fax' => $contact->fax,
            'comment' => $contact->comment,
            'comment_date' => $contact->comment_date,
            'type' => $contact->type_id ? [
                'id' => (int) $contact->type_id,
                'title' => Contact::CONTACT_TYPES[$contact->type_id],
            ] : null,
            'type_id' => $contact->type_id ? (int) $contact->type_id : null,
            'timezone' => $contact->timezone,
            'working_hours' => $contact->working_hours,
        ];
    }
}
