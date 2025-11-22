<?php

namespace App\Entities\Order\Parts;

use App\Foundations\Entities\BaseEntity;
use App\Foundations\Traits\Models\FullNameTrait;
use App\Foundations\ValueObjects\Email;

class EcommerceClientEntity extends BaseEntity
{
    use FullNameTrait;

    public string $first_name;
    public string $last_name;
    public Email $email;

    public static function make(?array $arr): ?self
    {
        if(!$arr) return null;

        $self = new self();

        $self->first_name = $arr['first_name'];
        $self->last_name = $arr['last_name'];
        $self->email = new Email($arr['email']);

        return $self;
    }
}
