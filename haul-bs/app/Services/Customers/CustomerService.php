<?php

namespace App\Services\Customers;

use App\Dto\Customers\CustomerDto;
use App\Dto\Customers\CustomerEcomDto;
use App\Models\Customers\Customer;
use Illuminate\Http\UploadedFile;

class CustomerService
{
    public function __construct()
    {}

    public function create(CustomerDto $dto, bool $ecomm = false): Customer
    {
        return make_transaction(function () use ($dto, $ecomm){
            $model = $this->fill(new Customer(), $dto);
            $model->has_ecommerce_account = $ecomm;

            $model->save();

            $model->tags()->sync($dto->tags);

            if(!empty($dto->files)){
                $this->uploadFiles($model, $dto->files);
            }

            return $model;
        });
    }

    public function update(Customer $model, CustomerDto $dto): Customer
    {
        return make_transaction(function () use ($model, $dto){
            $model = $this->fill($model, $dto);

            $model->save();

            $model->tags()->sync($dto->tags);

            if(!empty($dto->files)){
                $this->uploadFiles($model, $dto->files);
            }

            return $model;
        });
    }

    public function updateFromECom(Customer $model, CustomerEcomDto $dto): Customer
    {
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->email = $dto->email;
        $model->has_ecommerce_account = true;

        $model->save();

        return $model;
    }

    public function createFromECom(CustomerEcomDto $dto): Customer
    {
        $model = new Customer();
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->email = $dto->email;
        $model->type = $dto->type;
        $model->has_ecommerce_account = true;

        $model->save();

        $model->tags()->sync($dto->tags);

        return $model;
    }

    protected function fill(Customer $model, CustomerDto $dto): Customer
    {
        $model->first_name = $dto->firstName;
        $model->last_name = $dto->lastName;
        $model->email = $dto->email;
        $model->phone = $dto->phone;
        $model->phone_extension = $dto->phoneExtension;
        $model->phones = $dto->phones;
        $model->notes = $dto->notes;
        $model->origin_id = $dto->originId;
        $model->from_haulk = $dto->fromHaulk;
        $model->type = $dto->type;
        $model->sales_manager_id = $dto->salesManagedId;

        return $model;
    }

    public function delete(Customer $model): bool
    {
        return $model->delete();
    }

    public function uploadFile(Customer $model, UploadedFile $file): Customer
    {
        $model->addMediaWithRandomName(Customer::ATTACHMENT_COLLECTION_NAME, $file);

        return $model;
    }

    public function uploadFiles(Customer $model, array $attachments = []): void
    {
        foreach ($attachments as $attachment) {
            $this->uploadFile($model, $attachment);
        }
    }

    public function deleteFile(Customer $model, int $mediaId = 0): void
    {
        if ($model->media->find($mediaId)) {

            $model->deleteMedia($mediaId);

            return;
        }

        throw new \Exception(__('exceptions.file.not_found'));
    }
}
