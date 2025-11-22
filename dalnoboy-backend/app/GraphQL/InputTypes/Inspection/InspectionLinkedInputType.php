<?php


namespace App\GraphQL\InputTypes\Inspection;


use App\Enums\Permissions\GuardsEnum;
use App\Enums\Vehicles\VehicleFormEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use App\Rules\Inspections\InspectionLinkedRule;
use Core\Traits\Auth\AuthGuardsTrait;

class InspectionLinkedInputType extends BaseInputType
{
    use AuthGuardsTrait;

    public const NAME = 'InspectionLinkedInputType';

    private User|Admin|null $inspector;

    public function __construct()
    {
        $this->inspector = $this->getAuthUser();
    }

    public function fields(): array
    {
        return [
            'main_inspection_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Inspection::ruleExists()
                        ->where(function($query) {
                            if ($this->inspector->getGuard() === GuardsEnum::ADMIN) {
                                return $query;
                            }

                            return $query->where('inspector_id', $this->inspector?->id);
                        }),
                    new InspectionLinkedRule(VehicleFormEnum::MAIN())
                ]
            ],
            'trailer_inspection_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Inspection::ruleExists()
                        ->where(function($query) {
                            if ($this->inspector->getGuard() === GuardsEnum::ADMIN) {
                                return $query;
                            }

                            return $query->where('inspector_id', $this->inspector?->id);
                        }),
                    new InspectionLinkedRule(VehicleFormEnum::TRAILER())
                ]
            ],
        ];
    }
}
