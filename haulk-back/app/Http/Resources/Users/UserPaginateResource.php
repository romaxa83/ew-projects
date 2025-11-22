<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Files\ImageResource;
use App\Http\Resources\Tags\TagShortResource;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPaginateResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @OA\Schema(schema="UserRaw",
     *   @OA\Property(property="data", description="User paginated list", type="object", allOf={
     *      @OA\Schema(required={"id", "full_name", "first_name", "last_name", "email","status","security_level"},
     *          @OA\Property(property="id", type="integer", description="User id"),
     *          @OA\Property(property="full_name", type="string", description="User full name"),
     *          @OA\Property(property="first_name", type="string", description="User first name"),
     *          @OA\Property(property="last_name", type="string", description="User last name"),
     *          @OA\Property(property="email", type="string", description="User email"),
     *          @OA\Property(property="phone", type="string", description="User phone"),
     *          @OA\Property(property="phone_extension", type="string", description="User phone extension"),
     *          @OA\Property(property="status", type="string", description="User status"),
     *          @OA\Property(property="role_id", type="integer", description="Security level"),
     *          @OA\Property(property="owner_id", type="integer", description=""),
     *          @OA\Property(property="last_login", type="integer", description=""),
     *          @OA\Property(property="photo", type="object", description="image with different size", allOf={
     *              @OA\Schema(ref="#/components/schemas/Image")
     *          }),
     *          @OA\Property(property="tags", type="array", description="Vehicle Owner tags", @OA\Items(ref="#/components/schemas/TagRawShort")),
     *          @OA\Property(property="comments_count", type="integer", description="Comments count"),
     *       )
     *     }
     *   ),
     *   @OA\Property(property="links", ref="#/components/schemas/PaginationLinks",),
     *   @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta",),
     * )
     */
    public function toArray($request)
    {
        /** @var User $user */
        $user = $this;

        $data = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_extension' => $user->phone_extension,
            'status' => $user->status,
            'role_id' => $user->roles->first()->id,
            'owner_id' => $user->owner_id,
            'last_login' => $user->lastLogin ? $user->lastLogin->created_at->timestamp : null,
            $user->getImageField() => ImageResource::make($user->getFirstImage()),
            'tags' => TagShortResource::collection($this->tags),
        ];

        if ($user->isOwner()) {
            $data['hasRelatedOwnerTrucks'] = $user->ownerTrucks()->exists();
            $data['hasRelatedOwnerTrailers'] = $user->ownerTrailers()->exists();
            $data['comments_count'] = $user->comments()->count();
        }

        if ($user->isDriver()) {
            $data['hasRelatedDriverTruck'] = $user->truck()->exists();
            $data['hasRelatedDriverTrailer'] = $user->trailer()->exists();
            $data['comments_count'] = $user->comments()->count();
        }

        return $data;
    }
}
