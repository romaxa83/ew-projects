<?php

namespace App\Resources\IosLink;

use App\Models\User\IosLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="IosLink Resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="YJR6N43KXK7E"),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="link", type="string", example="https://apps.apple.com/redeem?code=YJR6N43KXK7E&ctx=apps"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="user_name", type="string", example="cubic"),
 * )
 */
class IosLinkResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     *
     * @SWG\Definition(definition="IosLink",
     *      @SWG\Property(property="id", type="integer", example = 1),
     *      @SWG\Property(property="code", type="string"),
     *      @SWG\Property(property="status", type="integer"),
     *      @SWG\Property(property="link", type="string"),
     *      @SWG\Property(property="user_id", type="integer"),
     *      @SWG\Property(property="user_name", type="string"),
     * )
     */
    public function toArray($request)
    {
        /** @var IosLink $iosLink */
        $iosLink = $this;

        return [
            'id' => $iosLink->id,
            'code' => $iosLink->code,
            'status' => $iosLink->status,
            'link' => $iosLink->link,
            'user_id' => $iosLink->user_id,
            'user_name' => $iosLink->user ? $iosLink->user->full_name : null
        ];
    }
}
