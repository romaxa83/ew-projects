<?php

namespace App\GraphQL\Mutations\FrontOffice\Favourites;

use App\Enums\Favourites\FavouriteModelsEnum;
use App\GraphQL\InputTypes\Favourites\FavouriteInput;
use App\Services\Favourites\FavouriteService;
use Closure;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rebing\GraphQL\Support\SelectFields;

class AddToFavouriteMutation extends BaseMutation
{
    public const NAME = 'addToFavourite';

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
            'favourite' => [
                'type' => FavouriteInput::nonNullType(),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        try {
            $favorable = FavouriteModelsEnum::getValue($args['favourite']['type']);

            $favorable = $favorable::query()->whereKey($args['favourite']['id'])->firstOrFail();

            return (bool)$this->service->add($this->user(), $favorable);
        } catch (ModelNotFoundException) {
            logger('Favourable model could not be found', $args);

            throw new TranslatedException(__('Favourable model could not be found'));
        }
    }
}
