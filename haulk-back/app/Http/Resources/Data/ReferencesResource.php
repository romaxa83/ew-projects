<?php


namespace App\Http\Resources\Data;


use Illuminate\Http\Resources\Json\JsonResource;

class ReferencesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="ReferencesResource",
     *    type="object",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        description="",
     *        @OA\Items(
     *            allOf={
     *                @OA\Schema(
     *                    @OA\Property(property="languages", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="name", type="string",),
     *                                    @OA\Property(property="slug", type="string",),
     *                                    @OA\Property(property="default", type="boolean",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="statesList", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="name", type="string",),
     *                                    @OA\Property(property="status", type="boolean",),
     *                                    @OA\Property(property="state_short_name", type="string",),
     *                                    @OA\Property(property="country_code", type="string",),
     *                                    @OA\Property(property="country_name", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="contactTypes", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="title", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="timezoneList", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="timezone", type="string",),
     *                                    @OA\Property(property="title", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="paymentMethods", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="title", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="vehicleTypes", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="title", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="expenseTypes", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="title", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                    @OA\Property(property="roles", type="array",
     *                        @OA\Items(
     *                            allOf={
     *                                @OA\Schema(
     *                                    @OA\Property(property="id", type="integer",),
     *                                    @OA\Property(property="name", type="string",),
     *                                    @OA\Property(property="guard_name", type="string",),
     *                                )
     *                            }
     *                        )
     *                    ),
     *                )
     *            }
     *        )
     *    ),
     * )
     *
     */
    public function toArray($request)
    {
        return [
            'languages' => $this['languages'] ?? [],
            'statesList' => $this['statesList'] ?? [],
            'contactTypes' => $this['contactTypes'] ?? [],
            'timezoneList' => $this['timezoneList'] ?? [],
            'paymentMethods' => $this['paymentMethods'] ?? [],
            'vehicleTypes' => $this['vehicleTypes'] ?? [],
            'expenseTypes' => $this['expenseTypes'] ?? [],
            'roles' => $this['roles'] ?? [],
        ];
    }
}
