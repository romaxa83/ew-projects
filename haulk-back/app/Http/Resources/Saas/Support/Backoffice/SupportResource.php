<?php

namespace App\Http\Resources\Saas\Support\Backoffice;

use App\Http\Resources\Saas\Support\SupportMessageResource;
use App\Models\Saas\Company\Company;
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
     *    schema="SupportRequestsBackOfficePaginatedResource",
     *    @OA\Property(
     *        property="data",
     *        type="array",
     *        @OA\Items(
     *              allOf = {
     *                  @OA\Schema (
     *                      @OA\Property (property="id", type="integer",),
     *                @OA\Property (property="author", type="object", ref="#/components/schemas/SupportRequestAuthorResource"),
     *                @OA\Property (property="comapny", type="object",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="status", type="integer",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="label", type="integer", nullable=true,
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="source", type="integer",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="created_at", type="number"),
     *                @OA\Property (property="closed_at", type="integer"),
     *                @OA\Property (property="closed_by", type="object",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="full_name", type="string",),
     *                            @OA\Property (property="is_support_employee", type="boolean",),
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="closing_reason", type="string", nullable=true),
     *                @OA\Property (property="subject", type="string",),
     *                @OA\Property (property="message", type="object", ref="#/components/schemas/SupportMessageRawResource"),
     *                @OA\Property (property="attachments", nullable=true, type="array", @OA\Items(ref="#/components/schemas/FileRaw"))
     *                  )
     *              }
     *        )
     *    ),
     *    @OA\Property(
     *        property="links",
     *        ref="#/components/schemas/PaginationLinks",
     *    ),
     *    @OA\Property(
     *        property="meta",
     *        ref="#/components/schemas/PaginationMeta",
     *    ),
     * )
     *
     * @OA\Schema(
     *    schema="SupportRequestBackofficeResource",
     *    @OA\Property(
     *        property="data",
     *        type="object",
     *        allOf = {
     *            @OA\Schema (
     *                @OA\Property (property="id", type="integer",),
     *                @OA\Property (property="author", type="object", ref="#/components/schemas/SupportRequestAuthorResource"),
     *                @OA\Property (property="comapny", type="object",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="status", type="integer",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="label", type="integer", nullable=true,
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="source", type="integer",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="id", type="integer",),
     *                            @OA\Property (property="name", type="string",)
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="created_at", type="number"),
     *                @OA\Property (property="closed_at", type="integer"),
     *                @OA\Property (property="closed_by", type="object",
     *                    allOf = {
     *                        @OA\Schema (
     *                            @OA\Property (property="full_name", type="string",),
     *                            @OA\Property (property="is_support_employee", type="boolean",),
     *                        )
     *                    }
     *                ),
     *                @OA\Property (property="closing_reason", type="string", nullable=true),
     *                @OA\Property (property="subject", type="string",),
     *                @OA\Property (property="message", type="object", ref="#/components/schemas/SupportMessageRawResource"),
     *                @OA\Property (property="attachments", nullable=true, type="array", @OA\Items(ref="#/components/schemas/FileRaw"))
     *            )
     *        }
     *    )
     * )
     */
    public function toArray($request): array
    {
        /**@var $supportRequest SupportRequest*/
        $supportRequest = $this;

        /**@var Company $company*/
        $company = $supportRequest->user ? $supportRequest->user->getCompany() : null;

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
            'label' => $supportRequest->label !== null ? [
                'id' => $supportRequest->label,
                'name' => SupportRequest::LABELS_DESCRIPTION[$supportRequest->label],
            ] : null,
            'source' => [
                'id' => $supportRequest->source,
                'name' => SupportRequest::SOURCES_DESCRIPTION[$supportRequest->source],
            ],
            'company' => $company !== null ? [
                'id' => $company->id,
                'name' => $company->name
            ] : null,
            'author' => $supportRequest->getAuthorData(),
            'manager' => $supportRequest->getManagerData(),
            'created_at' => Carbon::parse($supportRequest->created_at)->timestamp,
            'closed_at' => $supportRequest->closed_at,
            'closed_by' => $supportRequest->closed_by ? [
                'full_name' => $supportRequest->closed_by,
                'is_support_employee' => $supportRequest->closed_by_support_employee,
            ] : null,
            'closing_reason' => $supportRequest->status === SupportRequest::STATUS_CLOSED ? $supportRequest->closing_reason : null,
            'subject' => $supportRequest->subject,
        ];

        return array_merge(
            $response,
            $question
        );
    }
}
