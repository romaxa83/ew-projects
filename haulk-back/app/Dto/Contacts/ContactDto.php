<?php

namespace App\Dto\Contacts;

use App\Dto\BaseDto;
use Illuminate\Support\Collection;

/**
 * @property-read string $fullName
 * @property-read string $address
 * @property-read string $city
 * @property-read int $stateId
 * @property-read string|null $comment
 * @property-read string $zip
 * @property-read string|null $phone
 * @property-read string|null $phoneExtension
 * @property-read string|null $phoneName
 * @property-read Collection<PhoneDto>|PhoneDto[]|null $phones
 * @property-read string|null $email
 * @property-read string|null $fax
 * @property-read int $typeId
 * @property-read string $timezone
 * @property-read Collection<WorkingHourDto>|WorkingHourDto[]|null $workingHours
 */
class ContactDto extends BaseDto
{
    protected string $fullName;
    protected string $address;
    protected string $city;
    protected int $stateId;
    protected ?string $comment;
    protected string $zip;
    protected ?string $phone;
    protected ?string $phoneExtension;
    protected ?string $phoneName;
    protected ?Collection $phones = null;
    protected ?string $email;
    protected ?string $fax;
    protected int $typeId;
    protected string $timezone;
    protected ?Collection $workingHours;

    public static function init(array $args): self
    {
        $dto = new self();
        $dto->fullName = $args['full_name'];
        $dto->address = $args['address'];
        $dto->city = $args['city'];
        $dto->stateId = $args['state_id'];
        $dto->comment = $args['comment'] ?? null;
        $dto->zip = $args['zip'];
        $dto->phone = $args['phone'] ? preg_replace("/\D+/", "", $args['phone']) : null;
        $dto->phoneExtension = $args['phone_extension'] ?? null;
        $dto->phoneName = $args['phone_name'] ?? null;
        $dto->email = $args['email'] ?? null;
        $dto->fax = !empty($args['fax']) ? preg_replace("/\D+/", "", $args['fax']) : null;
        $dto->typeId = $args['type_id'];
        $dto->timezone = $args['timezone'];
        if (!empty($args['phones'])) {
            $dto->phones = collect();
            foreach ($args['phones'] as $phone) {
                $dto->phones->push(
                    PhoneDto::init($phone)
                );
            }
        }
        $dto->workingHours = !empty($args['working_hours']) ? WorkingHourDto::makeCollection(
            $args['working_hours']
        ) : null;
        return $dto;
    }
}
