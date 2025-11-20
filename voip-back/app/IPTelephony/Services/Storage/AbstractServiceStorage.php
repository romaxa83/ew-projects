<?php

namespace App\IPTelephony\Services\Storage;

use App\Helpers\DbConnections;
use Illuminate\Support\Collection;

abstract class AbstractServiceStorage
{
    public function getConectionDb()
    {
        $connection = match ($this->getDB()) {
            DbConnections::KAMAILIO => DbConnections::kamailio(),
            DbConnections::ASTERISK => DbConnections::asterisk(),
        };

        return $connection
            ->table($this->getTable());
    }

    abstract function getTable(): string;

    abstract function getDB(): string;

    public function getBy(string $field, string $value): ?object
    {
        return $this->getConectionDb()
            ->where($field, $value)
            ->first()
            ;
    }

    public function getAllBy(string $field, string $value): Collection
    {
        return $this->getConectionDb()
            ->where($field, $value)
            ->get()
            ;
    }

    public function getByFields(array $payload): ?object
    {
        $q = $this->getConectionDb();

        foreach ($payload as $field => $value){
            $q->where($field, $value);
        }

        return $q->first();
    }

    public function getAll(): Collection
    {
        return $this->getConectionDb()
            ->get()
            ;
    }

    public function insert(array $data): bool
    {
        return $this->getConectionDb()
            ->insert($data)
            ;
    }

    public function update(string $uuid, array $data): bool
    {
        return $this->getConectionDb()
            ->where('uuid', $uuid)
            ->update($data)
            ;
    }

    public function upsert(array $data, array $uniqFields, ?array $updateFields = null): bool
    {
        return $this->getConectionDb()
            ->upsert($data, $uniqFields, $updateFields);
    }

    public function updateOrInsert(array $attributes, array $value): bool
    {
        return $this->getConectionDb()
            ->updateOrInsert($attributes, $value)
            ;
    }

    public function delete(string $id): bool
    {
        return $this->getConectionDb()
            ->delete($id)
            ;
    }

    public function deleteBy(string $field, string $value): bool
    {
        return $this->getConectionDb()
            ->where($field, $value)
            ->delete()
            ;
    }
}

