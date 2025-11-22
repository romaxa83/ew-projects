<?php

namespace App\GraphQL\Mutations\FrontOffice\Dictionaries\TireModels;

use App\Dto\Dictionaries\TireModelDto;
use App\GraphQL\Mutations\Common\Dictionaries\TireModels\BaseTireModelCreateMutation;
use App\Models\Dictionaries\TireModel;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\SelectFields;

class TireModelCreateMutation extends BaseTireModelCreateMutation
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
    ): TireModel {
        return makeTransaction(
            fn() => $this->service->createOrUpdate(
                TireModelDto::byArgs($args['tire_model']),
                $this->user()
            )
        );
    }
}
