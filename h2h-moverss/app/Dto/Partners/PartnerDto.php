<?php

namespace App\Dto\Partners;

use App\Http\Requests\Partners\PartnerRequest;

final class PartnerDto
{
    public string $name;
    public int $divisionId;
    public ?string $contactPerson;
    public ?string $phone;
    public ?string $email;

    public static function byRequest(PartnerRequest $request): self
    {
        $data = $request->validated();
        if(
            !$request->has('division_id')
            && $request->session()->get('division.id')
        ){
            $data['division_id'] = $request->session()->get('division.id');
        }

        return self::byArgs($data);
    }

    public static function byArgs(array $data): self
    {
        $self = new self();

        $self->name = $data['name'];
        $self->divisionId = $data['division_id'] ?? null;
        $self->contactPerson = $data['contact_person'] ?? null;
        $self->phone = $data['phone'] ?? null;
        $self->email = $data['email'] ?? null;

        return $self;
    }

}

