<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol;

use App\Dto\Commercial\Commissioning\ProtocolDto;
use App\GraphQL\InputTypes\Commercial\Commissioning\ProtocolInput;
use App\GraphQL\Types\Commercial\Commissioning\ProtocolType;
use App\Models\Commercial\Commissioning\Protocol;
use App\Permissions\Commercial\Commissionings\Protocol\CreatePermission;
use App\Services\Commercial\CommercialProjectService;
use App\Services\Commercial\Commissioning\ProtocolService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;
use Illuminate\Validation\Rule;
use App\Rules\TranslationsArrayValidator;
use App\Models\Localization\Language;

class CreateMutation extends BaseMutation
{
    public const NAME = 'commissioningProtocolCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected ProtocolService $service,
        protected CommercialProjectService $projectService
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => ProtocolInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return ProtocolType::nonNullType();
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
    ): Protocol
    {
        $dto = ProtocolDto::byArgs($args['input']);

        $model = makeTransaction(
            fn(): Protocol => $this->service->create($dto)
        );

        makeTransaction(fn() => $this->projectService->addNewProtocol($model));

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.translations' => [new TranslationsArrayValidator()],
            'input.translations.*.language' => ['required', 'max:3', Rule::exists(Language::class, 'slug')],
            'input.translations.*.title' => ['required', 'max:250'],
            'input.translations.*.description' => ['nullable'],
        ];
    }
}

