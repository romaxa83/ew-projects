<?php

namespace App\GraphQL\Mutations\FrontOffice\Favourites;

use App\Enums\Favourites\FavouriteModelsEnum;
use App\GraphQL\Types\Enums\Favourites\FavouriteModelsEnumType;
use App\Services\Favourites\FavouriteService;
use Closure;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class RemoveAllFromFavouriteMutation extends BaseMutation
{
    public const NAME = 'removeAllFromFavourites';

    public function __construct(protected FavouriteService $service)
    {
        $this->setMemberGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return $this->authCheck();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'favourite_type' => [
                'type' => FavouriteModelsEnumType::nonNullType(),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $model = FavouriteModelsEnum::getValue($args['favourite_type']);

        $this->service->removeAll(
            $this->user(),
            new $model()
        );

        return true;
    }
}
