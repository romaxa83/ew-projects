<?php

namespace App\Foundations\Modules\Utils\Tokenizer\Traits;

use App\Foundations\Models\BaseAuthenticatableModel;
use App\Foundations\Modules\Utils\Tokenizer\Tokenizer;
use Carbon\CarbonImmutable;

trait EncryptToken
{
    protected function forEmail(BaseAuthenticatableModel $model): string
    {
        return Tokenizer::encryptToken([
            'model_id' => $model->id,
            'model_class' => $model::class,
            'time_at' => CarbonImmutable::now()->timestamp,
            'field_check_code' => 'email_verified_code',
            'code' => $model->email_verified_code,
        ]);
    }

    protected function forPassword(BaseAuthenticatableModel $model): string
    {
        return Tokenizer::encryptToken([
            'model_id' => $model->id,
            'model_class' => $model::class,
            'time_at' => CarbonImmutable::now()->timestamp,
            'field_check_code' => 'password_verified_code',
            'code' => $model->password_verified_code,
        ]);
    }
}

