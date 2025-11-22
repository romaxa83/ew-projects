<?php

namespace App\GraphQL\Mutations\FrontOffice\Dictionaries\TireSizes;

use App\Dto\Dictionaries\TireSizeDto;
use App\GraphQL\Mutations\Common\Dictionaries\TireSizes\BaseTireSizeCreateMutation;
use App\Models\Dictionaries\TireSize;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class TireSizeCreateMutation extends BaseTireSizeCreateMutation
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
    ): TireSize {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(
                TireSizeDto::byArgs($args['tire_size']),
                $this->user()
            )
        );
    }
}
