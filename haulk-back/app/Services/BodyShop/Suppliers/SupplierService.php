<?php

namespace App\Services\BodyShop\Suppliers;

use App\Dto\BodyShop\Suppliers\SupplierDto;
use App\Exceptions\HasRelatedEntitiesException;
use App\Models\BodyShop\Suppliers\Supplier;
use DB;
use Exception;
use Log;

class SupplierService
{
    public function create(SupplierDto $dto): Supplier
    {
        try {
            DB::beginTransaction();

            /** @var Supplier $supplier */
            $supplier = Supplier::query()->make($dto->getSupplierData());
            $supplier->saveOrFail();
            foreach ($dto->getSupplierContactsData() as $data) {
                $supplier->contacts()->create($data);
            }

            DB::commit();

            return $supplier;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(Supplier $supplier, SupplierDto $dto): Supplier
    {
        try {
            DB::beginTransaction();

            $supplier->update($dto->getSupplierData());
            $updatedIds = [];
            foreach ($dto->getSupplierContactsData() as $data) {
                if (isset($data['id'])) {
                    $contact = $supplier->contacts()->find($data['id']);
                    if ($contact) {
                        $contact->update($data);
                    }
                } else {
                    $contact = $supplier->contacts()->create($data);
                }
                $updatedIds[] = $contact->id;
            }
            $supplier->contacts()->whereNotIn('id', $updatedIds)->delete();

            DB::commit();

            return $supplier;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Supplier $supplier): Supplier
    {
        if ($supplier->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        $supplier->delete();

        return $supplier;
    }
}
