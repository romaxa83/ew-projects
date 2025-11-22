<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\Credentials;

use App\Dto\Commercial\CommercialCredentialsDto;
use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\InputTypes\Commercial\CommercialCredentialsInput;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectCreatePermission;
use App\Rules\Commercial\CommercialProjectForCredentialsRule;
use App\Services\Commercial\CommercialCredentialsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialCredentialsRequestMutation extends BaseMutation
{
    public const NAME = 'commercialCredentialsRequest';
    public const PERMISSION = CommercialProjectCreatePermission::KEY;

    public function __construct(private CommercialCredentialsService $service)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'input' => [
                'type' => CommercialCredentialsInput::nonNullType(),
            ]
        ];
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
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
    ): ResponseMessageEntity {
        return makeTransaction(
            fn() => $this->service->requestCredentials(
                $this->user(),
                CommercialCredentialsDto::byArgs($args['input'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn(): array => [
                'input.project_id' => [
                    'required',
                    'int',
                    new CommercialProjectForCredentialsRule($this->user())
                ],
            ],
        );
    }
}