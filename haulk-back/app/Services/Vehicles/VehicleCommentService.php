<?php

namespace App\Services\Vehicles;

use App\Models\Users\User;
use App\Models\Vehicles\Comments\Comment;
use App\Models\Vehicles\Vehicle;
use App\Services\Events\EventService;
use App\Services\Events\Vehicle\VehicleEventService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use DB;

class VehicleCommentService
{
    private ?User $user;

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function create(Vehicle $vehicle, array $attributes, ?string $timezone = null): Comment
    {
        try {
            DB::beginTransaction();

            $event = EventService::vehicle($vehicle)
                ->user($this->user);

            $comment = $vehicle->comments()->create(
                $attributes + [
                    'user_id' => $this->user->id,
                    'timezone' => $this->user->isBodyShopUser() ? null : $timezone,
                ]
            );

            $event->update(VehicleEventService::ACTION_VEHICLE_COMMENT_CREATED);

            DB::commit();

            return $comment;

        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function destroy(Vehicle $vehicle, Comment $comment): Comment
    {
        try {
            DB::beginTransaction();

            $event = EventService::vehicle($vehicle)
                ->user($this->user);

            $comment->delete();

            $event->update(VehicleEventService::ACTION_VEHICLE_COMMENT_DELETED);

            DB::commit();

            return $comment;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function getComments(Vehicle $vehicle): Collection
    {
        return $vehicle->comments()
            ->serviceContext($this->user->isBodyShopUser())
            ->orderBy('id')
            ->get();
    }
}
