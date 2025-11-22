<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Protocol;

use App\Dto\Commercial\Commissioning\ProtocolDto;
use App\GraphQL\InputTypes\Commercial\Commissioning\ProtocolInput;
use App\GraphQL\Types\Commercial\Commissioning\ProtocolType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Protocol;
use App\Models\Localization\Language;
use App\Permissions\Commercial\Commissionings\Protocol\UpdatePermission;
use App\Repositories\Commercial\Commissioning\ProtocolRepository;
use App\Rules\TranslationsArrayValidator;
use App\Services\Commercial\Commissioning\ProtocolService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdateMutation extends BaseMutation
{
    public const NAME = 'commissioningProtocolUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected ProtocolService $service,
        protected ProtocolRepository $repo
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Protocol::class, 'id')],
            ],
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
        /** @var $model Protocol */
        $model = $this->repo->getByFields(['id' => $args['id']]);

        $model = makeTransaction(
            fn(): Protocol => $this->service->update($model, $dto)
        );

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

