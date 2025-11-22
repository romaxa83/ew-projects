<?php

namespace App\Http\Resources\Saas\Support\Crm;

use App\Http\Resources\Saas\Support\SupportMessageResource;
use App\Models\Saas\Support\SupportRequest;
use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class SupportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     *
     * @OA\Schema(
     *    schema="SupportRequestsCrmPaginatedResource",
     *    @OA\Property (
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *             allOf = {
     *                  @OA\Schema (
     *                      @OA\Property (property="id", type="integer",),
     *                      @OA\Property (property="status", type="object", allOf={
     *                          @OA\Schema (
     *                              @OA\Property (property="id", type="integer",),
     *                              @OA\Property (property="name", type="string",),
     *                          )
     *                      }),
     *                      @OA\Property (property="created_at", type="number"),
     *                @OA\Property (property="closed_at", type="integer"),
     *                @OA\Property (property="closed_by", type="object",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="full_name", type="string",),
     *                            @OA\Property (property="is_support_employee", type="boolean",),
     *                        )
     *                    }
     *                ),
     *                      @OA\Property (property="author", type="object", nullable=true,),
     *                      @OA\Property (property="subject", type="string",),
     *                      @OA\Property (property="message", type="object", ref="#/components/schemas/SupportMessageRawResource"),
     *                      @OA\Property (property="attachments", nullable=true, type="array", @OA\Items(ref="#/components/schemas/FileRaw"))
     *                  )
     *             }
     *          )
     *    ),
     *    @OA\Property (
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property (
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     * @OA\Schema(
     *    schema="SupportRequestCrmResource",
     *    @OA\Property (
     *        property="data",
     *        type="object",
     *        allOf = {
     *              @OA\Schema (
     *                  @OA\Property (property="id", type="integer",),
     *                  @OA\Property (property="status", type="object", allOf={
     *                      @OA\Schema (
     *                          @OA\Property (property="id", type="integer",),
     *                          @OA\Property (property="name", type="string",),
     *                      )
     *                  }),
     *                  @OA\Property (property="created_at", type="number"),
     *                @OA\Property (property="closed_at", type="integer"),
     *                @OA\Property (property="closed_by", type="object",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="full_name", type="string",),
     *                            @OA\Property (property="is_support_employee", type="boolean",),
     *                        )
     *                    }
     *                ),
     *                  @OA\Property (property="author", type="object", nullable=true, ref="#/components/schemas/SupportRequestAuthorResource"),
     *                  @OA\Property (property="subject", type="string",),
     *                  @OA\Property (property="message", type="object", ref="#/components/schemas/SupportMessageRawResource"),
     *                  @OA\Property (property="attachments", nullable=true, type="array", @OA\Items(ref="#/components/schemas/FileRaw"))
     *              )
     *        }
     *    )
     * )
     */
    public function toArray($request): array
    {
        /**@var $supportRequest SupportRequest*/
        $supportRequest = $this;

        $question = SupportMessageResource::make($supportRequest->question())->toArray($request);

        unset(
            $question['id'],
            $question['created_at'],
            $question['author'],
            $question['is_my_message']
        );

        $response = [
            'id' => $supportRequest->id,
            'status' => [
                'id' => $supportRequest->status,
                'name' => SupportRequest::STATUSES_DESCRIPTION[$supportRequest->status],
            ],
            'author' => $supportRequest->getAuthorData(),
            'created_at' => Carbon::parse($supportRequest->created_at)->timestamp,
            'closed_at' => $supportRequest->closed_at,
            'closed_by' => $supportRequest->closed_by ? [
                'full_name' => $supportRequest->closed_by,
                'is_support_employee' => $supportRequest->closed_by_support_employee,
            ] : null,
            'subject' => $supportRequest->subject,
        ];

        return array_merge(
            $response,
            $question
        );
    }
}
