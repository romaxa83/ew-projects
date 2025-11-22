<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\CommercialQuotes;

use App\Dto\Commercial\CommercialQuoteAdminDto;
use App\GraphQL\InputTypes\Commercial\CommercialQuoteAdminInput;
use App\GraphQL\Types\Commercial\CommercialQuoteType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CommercialQuote;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteUpdatePermission;
use App\Services\Commercial\CommercialQuoteService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialQuoteUpdateMutation extends BaseMutation
{
    public const NAME = 'commercialQuoteUpdate';

    public const PERMISSION = CommercialQuoteUpdatePermission::KEY;

    public function __construct(private CommercialQuoteService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(CommercialQuote::class, 'id')],
            ],
            'input' => [
                'type' => CommercialQuoteAdminInput::nonNullType()
            ],
        ];
    }

    public function type(): Type
    {
        return CommercialQuoteType::nonNullType();
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
    ): CommercialQuote {

        $dto = CommercialQuoteAdminDto::byArgs($args['input']);
        /** @var $model CommercialQuote */
        $model = CommercialQuote::find($args['id']);

        $model = makeTransaction(
            fn(): CommercialQuote => $this->service->update($model, $dto)
        );

        return $model;
    }
}
