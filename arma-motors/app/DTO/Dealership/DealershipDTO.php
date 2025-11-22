<?php

namespace App\DTO\Dealership;

use App\Models\Dealership\Department;
use App\Services\Dealership\Exception\DealershipException;
use App\Traits\AssetData;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class DealershipDTO
{
    use AssetData;

    private null|string $website;
    private int|string $brandId;
    private int $sort;
    private bool $active;
    private null|Point $location;
    private array $translations = [];
    private array $departments = [];
    private array $timeStep = [];
    private null|string $alias = null;

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'brandId');

        $self = new self();

        $self->brandId = $args['brandId'];
        $self->website = $args['website'] ?? null;
        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->alias = $args['alias'] ?? null;

        $self->location = null;
        if(self::checkFieldExist($args, 'lat') && self::checkFieldExist($args, 'lon')){
            $self->location = new Point(trim($args['lat']), trim($args['lon']), 4326);
        }

        foreach ($args['translations'] ?? [] as  $translation){
            $self->translations[] = DealershipTranslationDTO::byArgs($translation);
        }

        foreach ($args['timeStep'] ?? [] as  $step){
            $self->timeStep[] = TimeStepDTO::byArgs($step);
        }

        if(isset($args['departmentSales'])){
            $data = $args['departmentSales'];
            $data['type'] = Department::TYPE_SALES;
            $self->departments[] = DepartmentDTO::byArgs($data);
        } else {
            DealershipException::noDepartmentSalesData();
        }

        if(isset($args['departmentService'])){
            $data = $args['departmentService'];
            $data['type'] = Department::TYPE_SERVICE;
            $self->departments[] = DepartmentDTO::byArgs($data);
        } else {
            DealershipException::noDepartmentServiceData();
        }

        if(isset($args['departmentCash'])){
            $data = $args['departmentCash'];
            $data['type'] = Department::TYPE_CREDIT;
            $self->departments[] = DepartmentDTO::byArgs($data);
        } else {
            DealershipException::noDepartmentCreditData();
        }

        if(isset($args['departmentBody'])){
            $data = $args['departmentBody'];
            $data['type'] = Department::TYPE_BODY;
            $self->departments[] = DepartmentDTO::byArgs($data);
        } else {
            DealershipException::noDepartmentBodyData();
        }

        return $self;
    }

    public function getBrandId(): string|int
    {
        return $this->brandId;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getLocation(): null|Point
    {
        return $this->location;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function hasDepartments(): bool
    {
        return !empty($this->departments);
    }

    public function getDepartments(): array
    {
        return $this->departments;
    }

    public function hasTimeStep(): bool
    {
        return !empty($this->timeStep);
    }

    public function getTimeStep(): array
    {
        return $this->timeStep;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}

