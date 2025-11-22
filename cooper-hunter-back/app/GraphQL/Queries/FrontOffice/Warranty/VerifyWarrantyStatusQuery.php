<?php

namespace App\GraphQL\Queries\FrontOffice\Warranty;

use App\Entities\Warranty\WarrantyVerificationStatusEntity;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Warranty\WarrantyVerificationStatusType;
use App\Rules\ExistsRules\SerialNumberExistsRule;
use App\Services\Warranty\WarrantyService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class VerifyWarrantyStatusQuery extends BaseQuery
{
    public const NAME = 'verifyWarrantyStatus';

    public function __construct(protected WarrantyService $service)
    {
        $this->setMemberGuard();
    }

    public function args(): array
    {
        return [
            'serial_number' => [
                'type' => NonNullType::string(),
                'rules' => ['string', new SerialNumberExistsRule()],
            ],
        ];
    }

    public function type(): Type
    {
        return WarrantyVerificationStatusType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): WarrantyVerificationStatusEntity
    {
        $serialNumber = strtoupper($args['serial_number']);
        return $this->service->verifyBySerialNumber($serialNumber);
    }
}
