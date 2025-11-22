<?php

namespace Database\Factories;

use App\Contracts\Members\Member;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Database\Factories\Technicians\TechnicianFactory;
use Database\Factories\Users\UserFactory;

trait ForMemberTrait
{
    public function forMember(Member|UserFactory|TechnicianFactory $member): static
    {
        if ($member instanceof User) {
            return $this->forUser($member);
        }

        if ($member instanceof UserFactory) {
            return $this->forUser($member);
        }

        if ($member instanceof Technician) {
            return $this->forTechnician($member);
        }

        if ($member instanceof TechnicianFactory) {
            return $this->forTechnician($member);
        }

        return $this;
    }

    public function forUser(User|UserFactory|null $user = null): static
    {
        if (!$user) {
            $user = User::factory();
        }

        return $this->state(
            [
                'member_id' => $user,
                'member_type' => User::MORPH_NAME,
            ]
        );
    }

    public function forTechnician(Technician|TechnicianFactory|null $technician = null): static
    {
        if (!$technician) {
            $technician = Technician::factory();
        }

        return $this->state(
            [
                'member_id' => $technician,
                'member_type' => Technician::MORPH_NAME,
            ]
        );
    }
}
