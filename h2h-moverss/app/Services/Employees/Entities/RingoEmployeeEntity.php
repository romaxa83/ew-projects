<?php

namespace App\Services\Employees\Entities;

final class RingoEmployeeEntity
{
    private ?string $sipUsername = null;


    private function __construct()
    {}

    public static function fromRingoData(array $data = []): self
    {
        $self = new self();

        if(!empty($data)){

            $sip = $data['directions']['main'][0]['direction'] ?? null;

            if(is_null($sip)){
                $sip = $data['directions']['additional'][0]['direction'] ?? null;
            }

            $self->sipUsername = $sip;
        }

        return $self;
    }

    public function getSipUsername(): ?string
    {
        return $this->sipUsername;
    }
}
