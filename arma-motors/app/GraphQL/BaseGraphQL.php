<?php

namespace App\GraphQL;

use App\Exceptions\ErrorsCode;
use App\Traits\GraphqlResponse;
use Illuminate\Support\Facades\Validator;

class BaseGraphQL
{
    use GraphqlResponse;

    protected function validation(array $data, array $rule, $code = ErrorsCode::BAD_REQUEST)
    {
        $validator = Validator::make($data, $rule);

        if($validator->fails()){
            $message = current(current($validator->errors()->getMessages())) ?? '';

            throw new \InvalidArgumentException($message, $code);
        }
    }
}
