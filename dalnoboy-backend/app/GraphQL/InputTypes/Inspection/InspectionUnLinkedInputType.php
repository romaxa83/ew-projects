<?php


namespace App\GraphQL\InputTypes\Inspection;


use App\Enums\Permissions\GuardsEnum;
use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\NonNullType;
use App\Models\Admins\Admin;
use App\Models\Inspections\Inspection;
use App\Models\Users\User;
use App\Rules\Inspections\InspectionUnLinkedRule;
use Core\Traits\Auth\AuthGuardsTrait;

class InspectionUnLinkedInputType extends BaseInputType
{

    use AuthGuardsTrait;

    public const NAME = 'InspectionUnLinkedInputType';

    private User|Admin|null $inspector;

    public function __construct()
    {
        $this->inspector = $this->getAuthUser();
    }

    public function fields(): array
    {
        return [
            'inspection_id' => [
                'type' => NonNullType::id(),
                'description' => 'Main or Trailer vehicle inspection ID',
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
                    new InspectionUnLinkedRule()
                ]
            ]
        ];
    }
}
