<?php

namespace App\Services\Warranty;

use App\Contracts\Members\Member;
use App\Dto\Warranty\WarrantyCreateOnecDto;
use App\Dto\Warranty\WarrantyInfo\WarrantyAddressDto;
use App\Dto\Warranty\WarrantyRegistrationDto;
use App\Dto\Warranty\WarrantyUpdateOnecDto;
use App\Entities\Warranty\WarrantyProductInfo;
use App\Entities\Warranty\WarrantyUserInfo;
use App\Entities\Warranty\WarrantyVerificationStatusEntity;
use App\Enums\Projects\Systems\WarrantyStatus;
use App\Events\Warranty\WarrantyRegistrationProcessedEvent;
use App\Events\Warranty\WarrantyRegistrationRequestedEvent;
use App\Exceptions\Warranty\ProductNotRegisteredException;
use App\Models\Admins\Admin;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Projects\System;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Models\Warranty\WarrantyInfo\WarrantyAddress;
use App\Models\Warranty\WarrantyRegistration;
use App\Models\Warranty\WarrantyRegistrationUnitPivot;
use Core\Exceptions\TranslatedException;
use Illuminate\Database\Eloquent\Builder;
use JsonException;

class WarrantyService
{
    /**
     * Клиент попросил не выводить статус регистрации гарантии для старых регистраций.
     *
     * Старыми регистрациями для клиента являются те регистрации, которые были загружены на старый сайт (прод до переезда)
     *
     * @link https://wezom.worksection.com/project/292109/10699681/10747631/
     */
    public const CONSIDER_OLD_BEFORE = '2022-05-13 23:59:59';

    /**
     * @throws JsonException
     */
    public function registerByUnits(
        array $serialNumbers,
        WarrantyRegistrationDto $dto,
        ?Member $member = null
    ): WarrantyRegistration
    {
        $this->assertWarrantyNotExistsForSerials($serialNumbers);

        $productsWithSerials = ProductSerialNumber::query()
            ->whereIn('serial_number', $serialNumbers)
            ->simple()
            ->get();

        $warranty = new WarrantyRegistration();
        $warranty->warranty_status = WarrantyStatus::PENDING();

        $this->setMember($warranty, $member);
        $this->fill($warranty, $dto);

        $warranty->save();

        $this->createWarrantyAddress($warranty, $dto->getAddressDto());

        $warranty->unitsPivot()->createMany(stdCollectionToArray($productsWithSerials));

        event(new WarrantyRegistrationRequestedEvent($serialNumbers, $warranty->warranty_status));

        return $warranty;
    }

    protected function assertWarrantyNotExistsForSerials(array $serialNumbers): void
    {
        if ($this->isWarrantyExistsForSerials($serialNumbers)) {
            throw new TranslatedException(
                __('Warranty registration for these serial numbers has already been requested')
            );
        }
    }

    protected function isWarrantyExistsForSerials(array $serialNumbers): bool
    {
        return WarrantyRegistrationUnitPivot::query()
//            ->notDeleted()
            ->whereIn('serial_number', $serialNumbers)
            ->exists();
    }

    protected function setMember(WarrantyRegistration $warranty, ?Member $member = null): void
    {
        if (is_null($member)) {
            return;
        }

        $warranty->member_type = $member->getMorphType();
        $warranty->member_id = $member->getId();
    }

    protected function fill(
        WarrantyRegistration $warranty,
        WarrantyRegistrationDto $dto,
    ): void
    {
        $warranty->type = $dto->type;
        $warranty->commercial_project_id = $dto->commercialProjectID;
        $warranty->user_info = $dto->getUserInfo();
        $warranty->product_info = $dto->getProductInfo();
    }

    protected function createWarrantyAddress(
        WarrantyRegistration $warranty,
        WarrantyAddressDto $dto,
    ): WarrantyAddress
    {
        $address = new WarrantyAddress();
        $address->warranty_id = $warranty->id;
        $address->country_id = $dto->countryID;
        $address->state_id = $dto->stateID;
        $address->city = $dto->city;
        $address->street = $dto->street;
        $address->zip = $dto->zip;
        $address->save();

        return $address;
    }

    public function register(Member $member, System $system, WarrantyRegistrationDto $dto): WarrantyRegistration
    {
        $units = $system->units;

        $units = $units->pluck('unit')->map(
            static fn(SystemUnitPivot $item) => $item->only(['product_id', 'serial_number'])
        );

        $this->assertWarrantyNotExistsForSerials(
            $serialNumbers = $units->pluck('serial_number')
                ->toArray()
        );

        $warranty = new WarrantyRegistration();
        $warranty->warranty_status = WarrantyStatus::PENDING();
        $warranty->system_id = $system->id;

        $this->setMember($warranty, $member);
        $this->fill($warranty, $dto);

        $warranty->save();

        $this->createWarrantyAddress($warranty, $dto->getAddressDto());

        $warranty->unitsPivot()->createMany($units);

        $system->warranty_status = WarrantyStatus::PENDING();
        $system->save();

        event(new WarrantyRegistrationRequestedEvent($serialNumbers, $warranty->warranty_status));

        return $warranty;
    }

    public function resolveSystemWarrantyStatusBySerials(array $serialNumbers, WarrantyStatus $newStatus): void
    {
        $serialsCount = count($serialNumbers);

        $systems = System::query()
            ->where('warranty_status', '!=', $newStatus->value)
            ->withCount('units')
            ->whereHas(
                'units',
                static fn(Builder|Product $h) => $h
                    ->select('serial_number')
                    ->whereIn('serial_number', $serialNumbers)
                    ->groupBy('serial_number')
                    ->havingRaw('count(serial_number) = ?', [$serialsCount])
            );

        foreach ($systems->cursor() as $system) {
            if ($system->units_count !== $serialsCount) {
                continue;
            }

            $system->warranty_status = $newStatus;
            $system->save();
        }
    }

    public function verifyBySerialNumber(string $serial): WarrantyVerificationStatusEntity
    {
        $warranty = WarrantyRegistration::query()
//            ->where('warranty_status', '!=', WarrantyStatus::DELETE)
            ->whereHas(
                'unitsPivot',
                static fn(Builder|WarrantyRegistrationUnitPivot $b) => $b
                    ->where('serial_number', $serial)
            )
            ->first();


        if (is_null($warranty)) {
            return WarrantyVerificationStatusEntity::information(
                __('messages.warranty.not_registered', compact('serial'))
            );
        }

        $entity = WarrantyVerificationStatusEntity::information(
            $this->getWarrantyStatusInformation($warranty)
        )
            ->setRegistered(true)
            ->setProduct(
                $this->getProductBySerial($serial, $warranty)
            );

        if ($warranty->warranty_status->onWarranty()) {
            $entity->setPurchaseDate($warranty->product_info->purchase_date)
                ->setInstallationDate($warranty->product_info->installation_date);
        }

        return $entity;
    }

    public function getWarrantyStatusInformation(WarrantyRegistration $warranty): string
    {
        if ($warranty->created_at->lte(self::CONSIDER_OLD_BEFORE)) {
            return __('messages.warranty.registered_old');
        }

        return __('messages.warranty.registered', ['status' => $warranty->warranty_status->description]);
    }

    protected function getProductBySerial(string $serial, WarrantyRegistration $warrantyRegistration): Product
    {
        if ($product = $warrantyRegistration->unitsPivot->where('serial_number', $serial)->first()?->product) {
            return $product;
        }

        throw new ProductNotRegisteredException(
            __('messages.warranty.not_registered', compact('serial'))
        );
    }

    public function process(
        WarrantyRegistration $warranty,
        array $actualSerialNumbers,
        WarrantyUpdateOnecDto $dto
    ): WarrantyRegistration
    {
        $this->updateOnec($warranty, $dto);

        $warranty->save();

        event(new WarrantyRegistrationProcessedEvent($warranty, $actualSerialNumbers));

        return $warranty;
    }

    public function create(
        WarrantyCreateOnecDto $dto,
        array $actualSerialNumbers,
    ): WarrantyRegistration
    {
        foreach ($actualSerialNumbers as $k => $sn){
            $actualSerialNumbers[$k]['serial_number'] = strtoupper($sn['serial_number']);
        }

        $sns = array_column($actualSerialNumbers, 'serial_number');
        $register_sn = WarrantyRegistrationUnitPivot::query()
            ->whereIn('serial_number', $sns)
            ->simple()
            ->get()
            ->pluck('serial_number')
            ->toArray()
        ;

        if(!empty($register_sn)){
            throw new \Exception("There are already registered serial numbers - [" . implode(', ', $register_sn) . "]");
        }

        $model = $this->createOnec($dto);

        event(new WarrantyRegistrationProcessedEvent($model, $actualSerialNumbers));

        return $model;
    }

    public function createOnec(WarrantyCreateOnecDto $dto)
    {
        $state = State::query()->where('short_name', $dto->state)->first();
        $country = Country::query()->where('alias', 'usa')->first();

        $model = new WarrantyRegistration();
        $model->warranty_status = $dto->status;
        $model->notice = $dto->notice;
        $model->type = $dto->type;

        $model->user_info = WarrantyUserInfo::make([
            'first_name' => $dto->userFirstName,
            'last_name' => $dto->userLastName,
            'email' => $dto->userEmail,
            'company_name' => $dto->userCompanyName,
            'company_address' => $dto->userCompanyAddress,
        ], true);
        $model->product_info = WarrantyProductInfo::make([
            'purchase_date' => $dto->purchaseDate,
            'installation_date' => $dto->installationDate,
            'installer_license_number' => $dto->installationLicenseNumber,
            'purchase_place' => $dto->purchasePlace,
        ]);

        $model->system_id = $dto->systemID;
        if($dto->commercialProjectGuid){
            $c = CommercialProject::query()
                ->select('id')
                ->where('guid', $dto->commercialProjectGuid)
                ->firstOrFail();

            $model->commercial_project_id = $c->id;
        }
        if($dto->memberType && $dto->memberGuid){
            if($dto->memberType === User::MORPH_NAME){
                $user = User::query()->where('guid', $dto->memberGuid)->firstOrFail();
            } else {
                $user = Technician::query()->where('guid', $dto->memberGuid)->firstOrFail();
            }

            $model->member_type = $dto->memberType;
            $model->member_id = $user->id;
        }

        $model->save();

        $addr = new WarrantyAddress();
        $addr->warranty_id = $model->id;
        $addr->country_id = $country->id;
        $addr->state_id = $state->id;
        $addr->city = $dto->addressCity;
        $addr->street = $dto->addressStreet;
        $addr->zip = $dto->addressZip;
        $addr->save();

        return $model;
    }

    public function updateOnec(WarrantyRegistration $warranty, WarrantyUpdateOnecDto $dto, $save = false)
    {
        if($dto->status){
            $warranty->warranty_status = $dto->status;
        }
        if($dto->notice){
            $warranty->notice = $dto->notice;
        }
        if($dto->type){
            $warranty->type = $dto->type;
        }
        if($dto->userEmail){
            $warranty->user_info->email = $dto->userEmail;
        }
        if($dto->userFirstName){
            $warranty->user_info->first_name = $dto->userFirstName;
        }
        if($dto->userLastName){
            $warranty->user_info->last_name = $dto->userLastName;
        }
        if($dto->userCompanyAddress){
            $warranty->user_info->company_address = $dto->userCompanyAddress;
        }
        if($dto->userCompanyName){
            $warranty->user_info->company_name = $dto->userCompanyName;
        }
        if($dto->purchaseDate){
            $warranty->product_info->purchase_date = $dto->purchaseDate;
        }
        if($dto->purchasePlace){
            $warranty->product_info->purchase_place = $dto->purchasePlace;
        }
        if($dto->installationDate){
            $warranty->product_info->installation_date = $dto->installationDate;
        }
        if($dto->installationLicenseNumber){
            $warranty->product_info->installer_license_number = $dto->installationLicenseNumber;
        }
        if($dto->addressCity){
            $warranty->address->city = $dto->addressCity;
        }
        if($dto->addressStreet){
            $warranty->address->street = $dto->addressStreet;
        }
        if($dto->addressZip){
            $warranty->address->zip = $dto->addressZip;
        }
        if($dto->state){
            $state = State::query()->where('short_name', 'MI')->first();
            $warranty->address->state_id = $state->id;
        }

        if($save){
            $warranty->save();
        }

        $warranty->address->save();
    }

    public function assertCanRegisterSystem(System $system): bool
    {
        if ($system->units()->doesntExist()) {
            return false;
        }

        $this->assertWarrantyNotExistsForSerials(
            $this->extractSerialsFromSystem($system)
        );

        return true;
    }

    protected function extractSerialsFromSystem(System $system): array
    {
        return $system
            ->units()
            ->get()
            ->pluck('unit')
            ->pluck('serial_number')
            ->toArray();
    }

    public function resolveSystemWarrantyStatus(System $system): void
    {
        $warranty = $this->getWarrantyRegistrationBySerialSet(
            $this->extractSerialsFromSystem($system)
        );

        if (!$warranty) {
            return;
        }

        $system->warranty_status = $warranty->warranty_status;
        $system->save();
    }

    protected function getWarrantyRegistrationBySerialSet(array $serialNumbers): ?WarrantyRegistration
    {
        $unitsQuery = WarrantyRegistrationUnitPivot::query()
            ->select('warranty_registration_id')
            ->whereIn('serial_number', $serialNumbers)
            ->groupBy('warranty_registration_id')
            ->havingRaw('count(warranty_registration_id) = ?', [count($serialNumbers)])
            ->getQuery();

        $warranty = WarrantyRegistration::query()
            ->whereIn('id', $unitsQuery)
            ->first();

        if (!$warranty) {
            return null;
        }

        if ($warranty->units()->count() !== count($serialNumbers)) {
            return null;
        }

        return $warranty;
    }
}
