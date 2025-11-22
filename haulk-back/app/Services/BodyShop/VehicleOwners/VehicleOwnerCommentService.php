<?php

namespace App\Services\BodyShop\VehicleOwners;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\BodyShop\VehicleOwners\VehicleOwnerComment;
use App\Models\Users\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class VehicleOwnerCommentService
{
    private ?User $user;

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function create(VehicleOwner $vehicleOwner, array $attributes): VehicleOwnerComment
    {
        try {

            return $vehicleOwner->comments()->create(
                $attributes + [
                    'user_id' => $this->user->id,
                ]
            );

        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function destroy(VehicleOwner $vehicleOwner, VehicleOwnerComment $comment): VehicleOwnerComment
    {
        try {
            $comment->delete();

            return $comment;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function getComments(VehicleOwner $vehicleOwner): Collection
    {
        return $vehicleOwner->comments()
            ->with('user.roles')
            ->orderBy('id')
            ->get();
    }
}
