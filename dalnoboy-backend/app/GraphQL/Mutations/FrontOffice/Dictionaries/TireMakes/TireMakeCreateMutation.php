<?php

namespace App\GraphQL\Mutations\FrontOffice\Dictionaries\TireMakes;

use App\Dto\Dictionaries\TireMakeDto;
use App\GraphQL\Mutations\Common\Dictionaries\TireMakes\BaseTireMakeCreateMutation;
use App\Models\Dictionaries\TireMake;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class TireMakeCreateMutation extends BaseTireMakeCreateMutation
{
    protected function setGuard(): void
    {
        $this->setUserGuard();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): TireMake {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(
                TireMakeDto::byArgs($args['tire_make']),
                $this->user()
            )
        );
    }
}
