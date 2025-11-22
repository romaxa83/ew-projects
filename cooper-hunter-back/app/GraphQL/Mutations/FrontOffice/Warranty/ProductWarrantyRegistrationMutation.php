<?php

namespace App\GraphQL\Mutations\FrontOffice\Warranty;

use App\Dto\Warranty\WarrantyRegistrationDto;
use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\InputTypes\Warranty\AddressInfoInput;
use App\GraphQL\InputTypes\Warranty\ProductInfoInput;
use App\GraphQL\InputTypes\Warranty\TechnicianInfoInput;
use App\GraphQL\InputTypes\Warranty\UserInfoInput;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Repositories\Catalog\Product\ProductRepository;
use App\Rules\WarrantyRegistrations\UnitNotRegisteredYetRule;
use App\Rules\WarrantyRegistrations\WithoutDuplicateRule;
use App\Services\Warranty\WarrantyService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class ProductWarrantyRegistrationMutation extends BaseMutation
{
    public const NAME = 'productWarrantyRegistration';

    public function __construct(
        protected WarrantyService $service,
        protected ProductRepository $repo
    )
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::type();
    }

    public function args(): array
    {
        return [
            'serial_numbers' => [
                'type' => NonNullType::listOfString(),
            ],
            'user' => [
                'type' => UserInfoInput::type(),
                'rules' => ['required_without:technician', 'array']
            ],
            'technician' => [
                'type' => TechnicianInfoInput::type(),
                'rules' => ['required_without:user', 'array']
            ],
            'address' => [
                'type' => AddressInfoInput::nonNullType(),
            ],
            'product' => [
                'type' => ProductInfoInput::nonNullType(),
            ],
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ResponseMessageEntity
    {
        try {
            makeTransaction(
                fn() => $this->service->registerByUnits(
                    $args['serial_numbers'],
                    WarrantyRegistrationDto::byArgs($args),
                    $this->user(),
                )
            );

            return ResponseMessageEntity::success(
                __('Warranty registration request sent')
            );
        } catch (TranslatedException $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        } catch (Throwable $e) {
            logger($e);

            return ResponseMessageEntity::fail(
                __('Error sending registration request')
            );
        }
    }

    protected function rules(array $args = []): array
    {
        return [
            'serial_numbers' => ['required', 'array'],
            'serial_numbers.*' => [new WithoutDuplicateRule($args),new UnitNotRegisteredYetRule()]
        ];
    }
}
