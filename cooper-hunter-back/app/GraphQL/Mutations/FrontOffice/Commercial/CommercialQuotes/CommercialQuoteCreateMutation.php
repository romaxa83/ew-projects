<?php

namespace App\GraphQL\Mutations\FrontOffice\Commercial\CommercialQuotes;

use App\Dto\Commercial\CommercialQuoteDto;
use App\GraphQL\InputTypes\Commercial\CommercialQuoteInput;
use App\GraphQL\Types\Commercial\CommercialQuoteType;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialQuote;
use App\Permissions\Commercial\CommercialQuotes\CommercialQuoteCreatePermission;
use App\Services\Commercial\CommercialQuoteService;
use Core\GraphQL\Mutations\BaseMutation;
use Core\Traits\Auth\TechnicianCommercial;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CommercialQuoteCreateMutation extends BaseMutation
{
    public const NAME = 'commercialQuoteCreate';
    public const PERMISSION = CommercialQuoteCreatePermission::KEY;

    public function __construct(protected CommercialQuoteService $service)
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [
            'quote' => CommercialQuoteInput::nonNullType(),
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
    ): CommercialQuote
    {
        $this->isTechnicianCommercial();

        return makeTransaction(
            fn() => $this->service->create(
                CommercialQuoteDto::byArgs($args['quote'])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'quote.project_id' => ['required', 'integer', Rule::exists(CommercialProject::TABLE, 'id')],
            'quote.email' => ['required', 'email:filter'],
        ];
    }
}
