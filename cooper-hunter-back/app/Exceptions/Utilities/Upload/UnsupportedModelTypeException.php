<?php


namespace App\Exceptions\Utilities\Upload;


use Core\Exceptions\TranslatedException;

class UnsupportedModelTypeException extends TranslatedException
{

    public function __construct(string $modelType)
    {
        parent::__construct(
            trans('validation.custom.utilities.upload.unsupported_model', ['model_type' => $modelType])
        );
    }
}
