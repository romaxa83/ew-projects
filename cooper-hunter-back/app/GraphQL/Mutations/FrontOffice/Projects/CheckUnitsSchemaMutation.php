<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\GraphQL\InputTypes\Projects\Systems\CheckProjectSystemUnitInput;
use App\Models\Technicians\Technician;
use App\Permissions\Projects\ProjectUpdatePermission;
use App\Rules\Catalog\UnitSerialNumberRule;
use App\Rules\Catalog\UnitSerialNumberUniqueRule;
use App\Rules\Projects\SystemBelongsToMemberRule;
use App\Services\Projects\SystemService;
use App\Traits\Warranty\CheckSerialNumber;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CheckUnitsSchemaMutation extends BaseMutation
{
    use CheckSerialNumber;

    public const NAME = 'checkUnitsSchema';
    public const PERMISSION = ProjectUpdatePermission::KEY;

    public function __construct(protected SystemService $service)
    {
        $this->setMemberGuard();
    }

    public function args(): array
    {
        return [
            'system' => [
                'type' => CheckProjectSystemUnitInput::nonNullType(),
            ],
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        if($this->user() && $this->user() instanceof Technician){
            $units = data_get($args, 'system.units', []);
            $this->assetBySystemUnits($units);
        }

        return true;
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'system.id' => ['required', 'int', new SystemBelongsToMemberRule($this->user())],
                'system.units.*' => [
                    'required',
                    'array',
                    new UnitSerialNumberRule(),
                    (new UnitSerialNumberUniqueRule())
                        ->ignoreSystem($args['system']['id'])
                        ->uniqueForMember($this->user())
                ],
                'system.units.*.serial_number' => ['sometimes', 'distinct'],
            ]
        );
    }
}

