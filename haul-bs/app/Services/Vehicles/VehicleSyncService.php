<?php

namespace App\Services\Vehicles;

use App\Dto\Customers\CustomerDto;
use App\Dto\Tags\TagDto;
use App\Dto\Vehicles\TrailerDto;
use App\Dto\Vehicles\TruckDto;
use App\Enums\Customers\CustomerType;
use App\Enums\Tags\TagType;
use App\Enums\Vehicles\VehicleType;
use App\Foundations\Modules\Media\Dto\MediaSyncDto;
use App\Foundations\Modules\Media\Services\MediaService;
use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Customers\CustomerService;
use App\Services\Tags\TagService;
use Carbon\CarbonImmutable;

final class VehicleSyncService
{
    public function __construct(
        protected TruckService $truckService,
        protected TrailerService $trailerService,
        protected CustomerService $customerService,
        protected TagService $tagService,
        protected MediaService $mediaService,
    )
    {}

    public function setVehicles(array $data): void
    {
        foreach ($data as $item){
            $this->syncVehicle($item);
        }
    }

    public function createMedia(array $data, Truck|Trailer $model)
    {
        if(!empty($data)){
            foreach ($data as $mediaItem){
                $mediaDto = MediaSyncDto::byArgs([
                    'id' => data_get($mediaItem, 'id'),
                    'model_type' => $model::MORPH_NAME,
                    'model_id' => $model->id,
                    'collection_name' => $model::ATTACHMENT_COLLECTION_NAME,
                    'file_name' => data_get($mediaItem, 'file_name'),
                    'name' => data_get($mediaItem, 'name'),
                    'mime_type' => data_get($mediaItem, 'mime_type'),
                    'disk' => data_get($mediaItem, 'disk'),
                    'size' => data_get($mediaItem, 'size'),
                    'manipulations' => data_get($mediaItem, 'manipulations'),
                    'custom_properties' => data_get($mediaItem, 'custom_properties'),
                    'responsive_images' => data_get($mediaItem, 'responsive_images'),
                    'order_column' => data_get($mediaItem, 'order_column'),
                ]);
                $this->mediaService->createFromSync($mediaDto);
            }
        }
    }

    public function syncVehicle(array $data): Truck|Trailer
    {
        $companyData = $data['company'];
        $customerData = $data['customer'];
        $mediaData = $data['media'];

        $company = $this->syncCompany($companyData);
        $customer = $this->syncCustomer($customerData);

        if($data['vehicle_type'] == 'truck'){

            $truck = $this->syncTruck($data, $company, $customer);
            $this->createMedia($mediaData, $truck);

            return $truck;

        } else {

            $trailer = $this->syncTrailer($data, $company, $customer);
            $this->createMedia($mediaData, $trailer);

            return $trailer;
        }
    }

    public function syncCompany(array $data): Company
    {
        $company = Company::find($data['id']);
        if(!$company){
            $company = new Company();
            $company->id = $data['id'];
            $company->name = $data['name'];
            $company->save();
        } else {
            $company->name = $data['name'];
            $company->save();
        }

        return $company;
    }

    public function syncCustomer(array $data): Customer
    {

        $customerDto = CustomerDto::byArgs([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'phone_extension' => $data['phone_extension'],
            'phones' => $data['phones'],
            'email' => $data['email'],
            'origin_id' => $data['id'],
            'from_haulk' => true,
            'type' => CustomerType::Haulk(),
        ]);

        /** @var $customer Customer */
        if(
            $customer = Customer::query()
                ->where('email', $data['email'])
                ->first()
        ){
            if(!is_null($data['phone']) && $customer->phone->getValue() != $data['phone']){

                $existPhone = false;

                foreach ($customer->phones ?? [] as $phone){
                    if($phone['number'] == $data['phone']){
                        $existPhone = true;
                    }
                }

                if(!$existPhone){
                    $newPhone = [
                        [
                            'number' => $data['phone'],
                            'extension' => $data['phone_extension'] ?? null,
                        ]
                    ];
                    if(!is_null($customer->phones)){
                        $newPhone = array_merge($customer->phones, $newPhone);
                    }

                    $customer->update(['phones' => $newPhone]);
                }
            }

            return $customer;
        }


        /** @var $customer Customer */
        if(
            $customer = Customer::query()
                ->where('origin_id', $data['id'])
                ->first()
        ){
            $customer = $this->customerService->update($customer, $customerDto);
        } else {
            $customer = $this->customerService->create($customerDto);
        }

        return $customer;
    }

    public function syncTags(array $data): array
    {
        $tagsID = [];
        if(!empty($data)){
            foreach ($data as $tagItem){
                $tagDto = TagDto::byArgs([
                    'name' => $tagItem['name'],
                    'color' => $tagItem['color'],
                    'type' => TagType::TRUCKS_AND_TRAILER,
                    'origin_id' => $tagItem['id'],
                ]);

                /** @var $tag Tag */
                if(
                    $tag = Tag::query()
                        ->where('origin_id', $tagItem['id'])
                        ->first()
                ){
                    $tag = $this->tagService->update($tag, $tagDto);
                } else {
                    $tag = $this->tagService->createFromSync($tagDto);
                }
                $tagsID[] = $tag->id;
            }
        }

        return $tagsID;
    }
    public function syncTruck(
        array $data,
        Company $company,
        Customer $customer
    ): Truck
    {
        $dto = TruckDto::byArgs([
            'vin' => data_get($data, 'vin'),
            'unit_number' => data_get($data, 'unit_number'),
            'make' => data_get($data, 'make'),
            'model' => data_get($data, 'model'),
            'year' => data_get($data, 'year'),
            'color' => data_get($data, 'color'),
            'gvwr' => data_get($data, 'gvwr'),
            'type' => data_get($data, 'type'),
            'license_plate' => data_get($data, 'license_plate'),
            'temporary_plate' => data_get($data, 'temporary_plate'),
            'notes' => data_get($data, 'notes'),
            'created_at' => CarbonImmutable::createFromTimestamp(data_get($data, 'notes')),
            'company_id' => $company->id,
            'origin_id' => data_get($data, 'id'),
            'owner_id' => $customer->id,
        ]);

        if(
            $truck = Truck::query()
                ->withTrashed()
                ->where('origin_id', data_get($data, 'id'))
                ->first()
        ){

            $truck = $this->truckService->updateFromSync($truck, $dto);
            $truck->clearMediaCollectionExcept(Truck::ATTACHMENT_COLLECTION_NAME);
        } else {
            $truck =  $this->truckService->createFromSync($dto);
        }

        return $truck;
    }

    public function syncTrailer(
        array $data,
        Company $company,
        Customer $customer
    ): Trailer
    {
        $dto = TrailerDto::byArgs([
            'vin' => data_get($data, 'vin'),
            'unit_number' => data_get($data, 'unit_number'),
            'make' => data_get($data, 'make'),
            'model' => data_get($data, 'model'),
            'year' => data_get($data, 'year'),
            'color' => data_get($data, 'color'),
            'gvwr' => data_get($data, 'gvwr'),
            'type' => VehicleType::VEHICLE_TYPE_OTHER,
            'license_plate' => data_get($data, 'license_plate'),
            'temporary_plate' => data_get($data, 'temporary_plate'),
            'notes' => data_get($data, 'notes'),
            'created_at' => CarbonImmutable::createFromTimestamp(data_get($data, 'notes')),
            'company_id' => $company->id,
            'origin_id' => data_get($data, 'id'),
            'owner_id' => $customer->id,
        ]);

        if(
            $trailer = Trailer::query()
                ->withTrashed()
                ->where('origin_id', data_get($data, 'id'))
                ->first()
        ){
            $trailer = $this->trailerService->updateFromSync($trailer, $dto);
            $trailer->clearMediaCollectionExcept(Trailer::ATTACHMENT_COLLECTION_NAME);
        } else {
            $trailer = $this->trailerService->createFromSync($dto);
        }

        return $trailer;
    }
}
