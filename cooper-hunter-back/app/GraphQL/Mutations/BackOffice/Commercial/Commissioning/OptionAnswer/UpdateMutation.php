<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer;

use App\Dto\Commercial\Commissioning\OptionAnswerDto;
use App\GraphQL\InputTypes\Commercial\Commissioning\OptionsAnswerUpdateInput;
use App\GraphQL\Types\Commercial\Commissioning\OptionAnswerType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Localization\Language;
use App\Permissions\Commercial\Commissionings\Question\UpdatePermission;
use App\Repositories\Commercial\Commissioning\OptionAnswerRepository;
use App\Rules\TranslationsArrayValidator;
use App\Services\Commercial\Commissioning\OptionAnswerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdateMutation extends BaseMutation
{
    public const NAME = 'commissioningOptionAnswerUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected OptionAnswerService $service,
        protected OptionAnswerRepository $repo
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(OptionAnswer::class, 'id')],
            ],
            'input' => OptionsAnswerUpdateInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return OptionAnswerType::nonNullType();
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
    ): OptionAnswer
    {
        $dto = OptionAnswerDto::byArgs($args['input']);
        /** @var $model OptionAnswer */
        $model = $this->repo->getByFields(['id' => $args['id']]);

        $model = makeTransaction(
            fn(): OptionAnswer => $this->service->update($model, $dto)
        );

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.translations' => [new TranslationsArrayValidator()],
            'input.translations.*.language' => ['required', 'max:3', Rule::exists(Language::class, 'slug')],
            'input.translations.*.text' => ['required'],
        ];
    }
}

