<?php

namespace App\Traits\Utilities;

use App\Contracts\Payment\PaymentModel;
use App\Dto\Utilities\Morph\MorphDto;
use App\Models\BaseAuthenticatable;
use App\Models\BaseModel;
use App\Providers\AppServiceProvider;

trait GetModelFromMorph
{
    public function morphModel(MorphDto $dto): BaseModel|BaseAuthenticatable|PaymentModel
    {
        $class = data_get(AppServiceProvider::morphs(), $dto->type);
        $model = $class::find($dto->id);

        return $model;
    }
}
