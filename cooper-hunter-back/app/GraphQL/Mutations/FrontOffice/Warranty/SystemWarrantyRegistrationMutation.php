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
use App\Models\Projects\System;
use App\Permissions\Projects\ProjectUpdatePermission;
use App\Rules\Projects\SystemBelongsToMemberRule;
use App\Services\Warranty\WarrantyService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SystemWarrantyRegistrationMutation extends BaseMutation
{
    public const NAME = 'systemWarrantyRegistration';
    public const PERMISSION = ProjectUpdatePermission::KEY;

    public function __construct(protected WarrantyService $service)
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
            'system_id' => [
                'type' => NonNullType::id(),
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
    ): ResponseMessageEntity {

        $system = System::query()->whereKey($args['system_id'])->firstOrFail();

        if ($system->warranty_status->requestSent()) {
            return ResponseMessageEntity::warning(
                __('Warranty registration request already sent.')
            );
        }

        try {
            makeTransaction(
                fn() => $this->service->register(
                    $this->user(),
                    $system,
                    WarrantyRegistrationDto::byArgs($args)
                )
            );

            return ResponseMessageEntity::success(
                __('Warranty registration request sent')
            );
        } catch (TranslatedException $e) {
            return ResponseMessageEntity::warning($e->getMessage());
        } catch (Throwable) {
            return ResponseMessageEntity::fail(
                __('Error sending registration request')
            );
        }
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'system_id' => ['required', 'int', new SystemBelongsToMemberRule($this->user())],
            ],
        );
    }
}
