<?php

namespace App\Services\Suppliers;

use App\Dto\Suppliers\SupplierContactDto;
use App\Dto\Suppliers\SupplierDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\Suppliers\Supplier;

class SupplierService
{
    public function __construct(
        protected SupplierContactService $supplierContactService
    )
    {}

    public function create(SupplierDto $dto): Supplier
    {
        try {
            \DB::beginTransaction();

            /** @var Supplier $model */
            $model = $this->fill(new Supplier(), $dto);
            $model->save();

            foreach ($dto->contacts as $item) {
                /** @var $item SupplierContactDto*/
                $this->supplierContactService->create($item, $model);
            }

            \DB::commit();

            return $model;
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(Supplier $model, SupplierDto $dto): Supplier
    {
        try {
            \DB::beginTransaction();

            $model = $this->fill($model, $dto);
            $model->save();

            $updatedIds = [];
            foreach ($dto->contacts as $item) {
                /** @var $item SupplierContactDto*/
                if ($item->id) {
                    $contact = $model->contacts->where('id', $item->id)->first();
                    if ($contact) {
                        $contact  = $this->supplierContactService->update($contact, $item, $model);
                    }
                } else {
                    $contact = $this->supplierContactService->create($item, $model);
                }
                $updatedIds[] = $contact->id;
            }

            $model->contacts()->whereNotIn('id', $updatedIds)->delete();

            \DB::commit();

            return $model->refresh();
        } catch (\Throwable $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    private function fill(Supplier $model, SupplierDto $dto): Supplier
    {
        $model->name = $dto->name;
        $model->url = $dto->url;

        return $model;
    }

    public function delete(Supplier $model): bool
    {
        if ($model->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        return $model->delete();
    }
}
