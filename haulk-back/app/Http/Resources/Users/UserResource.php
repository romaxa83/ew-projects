<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Files\FileResource;
use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Fueling\FuelCardPaginatedResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Http\Resources\Vehicles\Trailers\TrailerResource;
use App\Http\Resources\Vehicles\Trailers\TrailerResourceForOwner;
use App\Http\Resources\Vehicles\Trucks\TruckResource;
use App\Http\Resources\Vehicles\Trucks\TruckResourceForOwner;
use App\Models\Users\DriverInfo;
use App\Models\Users\DriverLicense;
use App\Models\Users\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="User", type="object", allOf={
     *      @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email","status","security_level"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="full_name", type="string", description="User full name"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="email", type="string", description="User email"),
     *          @OA\Property(property="phone", type="string", description="User phone"),
     *          @OA\Property(property="role_id", type="integer", description="User role id"),
     *          @OA\Property(property="status", type="string", description="User status"),
     *          @OA\Property(property="last_login", type="integer", description="Last login date in timestamp"),
     *          @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *          @OA\Property(property="phones", type="array", description="User aditional phones",
     *              @OA\Items(ref="#/components/schemas/PhonesRaw")
     *          ),
     *          @OA\Property(property="photo", type="object", description="image with different size", allOf={
     *              @OA\Schema(ref="#/components/schemas/Image")
     *          }),
     *
     *          @OA\Property(property="owner_id", type="integer", description="Owner for user(if user is driver)"),
     *          @OA\Property(property="driver_rate", type="integer", description="Driver rate (if user is driver)"),
     *          @OA\Property(property="notes", type="integer", description="Driver notes (if user is driver)"),
     *          @OA\Property(property="drivers", type="array", description="User attachments", @OA\Items(ref="#/components/schemas/DispatcherDriversListResource")),
     *          @OA\Property(property="attachments", type="array", description="User attachments", @OA\Items(ref="#/components/schemas/FileRaw")),
     *          @OA\Property(property="can_check_orders", type="boolean", description="Dispatcher can chack orders"),
     *          @OA\Property(property="deleted_at", type="integer", description="Time of deleted user", nullable=true),
     *          @OA\Property(property="truck", ref="#/components/schemas/Truck",),
     *          @OA\Property(property="trailer", ref="#/components/schemas/Trailer",),
     *          @OA\Property(property="tags", type="array", description="Vehicle Owner tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *          @OA\Property(property="owner_trucks", type="array", description="Owner trucks",
     *              @OA\Items(ref="#/components/schemas/TruckForOwner")
     *          ),
     *          @OA\Property(property="fuel_cards", type="array", description="Fuel cards",
     *              @OA\Items(ref="#/components/schemas/FuelCardResource")
     *           ),
     *          @OA\Property(property="owner_trailers", type="array", description="Owner trailers",
     *              @OA\Items(ref="#/components/schemas/TrailerForOwner")
     *          ),
     *          @OA\Property(property="hasDriverTrucksHistory", type="boolean", description="Has driver trucks history (excluding current driver truck)"),
     *          @OA\Property(property="hasDriverTrailersHistory", type="boolean", description="Has driver trailers history (excluding current driver trailer)"),
     *          @OA\Property(property="hasOwnerTrucksHistory", type="boolean", description="Has owner trucks history (excluding current owner trucks)"),
     *          @OA\Property(property="hasOwnerTrailersHistory", type="boolean", description="Has owner trailers history (excluding current owner trailers)"),
     *          @OA\Property(property="medical_card", type="object", description="Driver medical card",
     *              allOf={
     *                  @OA\Schema(
     *                      required={},
     *                      @OA\Property(property="card_number", type="string", description="Medical card number"),
     *                      @OA\Property(property="issuing_date", type="string", description="Medical card issuing date, format=m/d/Y"),
     *                      @OA\Property(property="expiration_date", type="string", description="Medical card expiration date, format=m/d/Y"),
     *                      @OA\Property(property="document", type="object", description="Medical card document",
     *                          @OA\Schema(ref="#/components/schemas/File")
     *                      ),
     *                  )
     *              }
     *          ),
     *          @OA\Property(property="mvr", type="object", description="Driver mvr",
     *              allOf={
     *                  @OA\Schema(
     *                      required={},
     *                      @OA\Property(property="reported_date", type="string", description="MVR reported date, format=m/d/Y"),
     *                      @OA\Property(property="document", type="object", description="Medical card document",
     *                          @OA\Schema(ref="#/components/schemas/File")
     *                      ),
     *                  )
     *              }
     *          ),
     *          @OA\Property(property="driver_license", type="object", description="Driver license",
     *              allOf={
     *                  @OA\Schema(
     *                      required={},
     *                      @OA\Property(property="license_number", type="string", description="Driver license card number"),
     *                      @OA\Property(property="issuing_date", type="string", description="Driver license issuing date, format=m/d/Y"),
     *                      @OA\Property(property="expiration_date", type="string", description="Driver license expiration date, format=m/d/Y"),
     *                      @OA\Property(property="issuing_state_id", type="integer", description="Driver license issuing state id"),
     *                      @OA\Property(property="category", type="string", description="Driver license category"),
     *                      @OA\Property(property="category_name", type="string", description="Driver license category name (for Other category)"),
     *                      @OA\Property(property="document", type="object", description="Driver license document",
     *                          @OA\Schema(ref="#/components/schemas/File")
     *                      ),
     *                  )
     *              }
     *          ),
     *          @OA\Property(property="previous_driver_license", type="object", description="Previous Driver license",
     *              allOf={
     *                  @OA\Schema(
     *                      required={},
     *                      @OA\Property(property="license_number", type="string", description="Driver license card number"),
     *                      @OA\Property(property="issuing_date", type="string", description="Driver license issuing date, format=m/d/Y"),
     *                      @OA\Property(property="expiration_date", type="string", description="Driver license expiration date, format=m/d/Y"),
     *                      @OA\Property(property="is_usa", type="boolean", description="Is Driver license of USA"),
     *                      @OA\Property(property="issuing_country", type="string", description="Driver license country"),
     *                      @OA\Property(property="issuing_state_id", type="integer", description="Driver license issuing state id"),
     *                      @OA\Property(property="category", type="string", description="Driver license category"),
     *                      @OA\Property(property="category_name", type="string", description="Driver license category name (for Other category)"),
     *                      @OA\Property(property="document", type="object", description="Driver license document",
     *                          @OA\Schema(ref="#/components/schemas/File")
     *                      ),
     *                  )
     *              }
     *          ),
     *          @OA\Property(property="has_company", type="boolean", description="Is Driver has company"),
     *          @OA\Property(property="company_info", type="object", description="Driver company info",
     *              allOf={
     *                  @OA\Schema(
     *                      required={},
     *                      @OA\Property(property="name", type="string", description="Driver company name"),
     *                      @OA\Property(property="ein", type="string", description="Driver company ein"),
     *                      @OA\Property(property="address", type="string", description="Driver company address"),
     *                      @OA\Property(property="city", type="string", description="Driver company city"),
     *                      @OA\Property(property="zip", type="string", description="Driver license country"),
     *                  )
     *              }
     *          ),
     *      )
     * })
     */
    public function toArray($request)
    {
        $role = $this->roles[0];
        return $this->addInfoForRole(
            [
                'id' => $this->id,
                'full_name' => $this->full_name,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'phone_extension' => $this->phone_extension,
                'phones' => $this->phones,
                'status' => $this->status,
                'role_id' => $role->id,
                'last_login' => $this->lastLogin ? $this->lastLogin->created_at->timestamp : null,
                'drivers' => $role->name !== User::DRIVER_ROLE ? DispatcherDriversListResource::collection(
                    $this->drivers
                ) : null,
                $this->getImageField() => ImageResource::make($this->getFirstImage()),
                User::ATTACHMENT_COLLECTION_NAME => FileResource::collection($this->getAttachments()),
                'deleted_at' => $this->deleted_at instanceof Carbon ? $this->deleted_at->getTimestamp() : null,
            ]
        );
    }

    //В будущем нужно заменить на фильтрацию по пермишенам
    protected function addInfoForRole(array $attributes): array
    {
        switch ($this->getRoleName()) {
            case User::SUPERADMIN_ROLE:
                return $attributes + $this->additionalSuperAdminInfo();

            case User::ADMIN_ROLE:
                return $attributes + $this->additionalAdminInfo();

            case User::DRIVER_ROLE:
                return $attributes + $this->additionalDriverInfo();

            case User::OWNER_DRIVER_ROLE:
                return $attributes + $this->additionalDriverInfo() + $this->additionalOwnerInfo();

            case User::OWNER_ROLE:
                return $attributes + $this->additionalOwnerInfo();

            case User::DISPATCHER_ROLE:
                return $attributes + $this->additionalDispatcherInfo();
        }

        return $attributes;
    }

    protected function additionalSuperAdminInfo(): array
    {
        return [
            'can_check_orders' => (bool)$this->can_check_orders,
        ];
    }

    protected function additionalAdminInfo(): array
    {
        return [
            'can_check_orders' => (bool)$this->can_check_orders,
        ];
    }

    protected function additionalDriverInfo(): array
    {
        $data = [
            'owner_id' => $this->owner_id,
            'fuel_cards' => FuelCardPaginatedResource::collection($this->fuelCards),
            'driver_rate' => $this->driverInfo->driver_rate ?? null,
            'notes' => $this->driverInfo->notes ?? null,
            'truck' => $this->truck ? TruckResource::make($this->truck) : null,
            'trailer' => $this->trailer ? TrailerResource::make($this->trailer) : null,
            'hasDriverTrucksHistory' => $this->hasDriverTrucksHistory(),
            'hasDriverTrailersHistory' => $this->hasDriverTrailersHistory(),
        ];

        if ($this->driverInfo) {
            $driverLicense = $this->getCurrentDriverLicense();
            $previousDriverLicense = $this->getPreviousDriverLicense();
            $data += [
                'medical_card' => [
                    'card_number' => $this->driverInfo->medical_card_number,
                    'issuing_date' => $this->driverInfo->getMedicalCardIssuingDate(),
                    'expiration_date' => $this->driverInfo->getMedicalCardExpirationDate(),
                    'document' => $this->driverInfo->getMedicalCardDocument()
                        ? FileResource::make($this->driverInfo->getMedicalCardDocument())
                        : null,
                ],
                'mvr' => [
                    'reported_date' => $this->driverInfo->getMvrReportedDate(),
                    'document' => $this->driverInfo->getMvrDocument()
                        ? FileResource::make($this->driverInfo->getMvrDocument())
                        : null,
                ],
                'driver_license' => $driverLicense
                    ? [
                        'license_number' => $driverLicense->license_number,
                        'issuing_state_id' => $driverLicense->issuing_state_id,
                        'issuing_date' => $driverLicense->getIssuingDate(),
                        'expiration_date' => $driverLicense->getExpirationDate(),
                        'category' => $driverLicense->category,
                        'category_name' => $driverLicense->category_name,
                        'document' => $driverLicense->getDocument()
                            ? FileResource::make($driverLicense->getDocument())
                            : null
                    ]
                    : null,
                'previous_driver_license' => $previousDriverLicense
                    ? [
                        'license_number' => $previousDriverLicense->license_number,
                        'is_usa' => (bool)$previousDriverLicense->issuing_state_id,
                        'issuing_country' => $previousDriverLicense->issuing_country,
                        'issuing_state_id' => $previousDriverLicense->issuing_state_id,
                        'issuing_date' => $previousDriverLicense->getIssuingDate(),
                        'expiration_date' => $previousDriverLicense->getExpirationDate(),
                        'category' => $previousDriverLicense->category,
                        'category_name' => $previousDriverLicense->category_name,
                        'document' => $previousDriverLicense->getDocument()
                            ? FileResource::make($previousDriverLicense->getDocument())
                            : null
                    ]
                    : null,
                'has_company' => $this->driverInfo->has_company,
                'company_info' => $this->driverInfo->has_company
                    ? [
                        'name' => $this->driverInfo->company_name,
                        'ein' => $this->driverInfo->company_ein,
                        'address' => $this->driverInfo->company_address,
                        'city' => $this->driverInfo->company_city,
                        'zip' => $this->driverInfo->company_zip,
                    ]
                    : null,
            ];
        }

        return $data;
    }

    protected function additionalDispatcherInfo(): array
    {
        return [
            'can_check_orders' => (bool)$this->can_check_orders,
        ];
    }

    protected function additionalOwnerInfo(): array
    {
        return [
            'tags' => TagShortResource::collection($this->tags),
            'owner_trucks' => $this->ownerTrucks ? TruckResourceForOwner::collection($this->ownerTrucks) : null,
            'owner_trailers' => $this->ownerTrailers ? TrailerResourceForOwner::collection($this->ownerTrailers) : null,
            'hasOwnerTrucksHistory' => $this->hasOwnerTrucksHistory(),
            'hasOwnerTrailersHistory' => $this->hasOwnerTrailersHistory(),
        ];
    }
}
