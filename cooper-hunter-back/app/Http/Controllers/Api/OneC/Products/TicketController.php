<?php

namespace App\Http\Controllers\Api\OneC\Products;

use App\Enums\Tickets\TicketStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Catalog\Tickets\TicketCreateRequest;
use App\Http\Requests\Api\OneC\Catalog\Tickets\TicketUpdateCodeRequest;
use App\Http\Requests\Api\OneC\Catalog\Tickets\TicketUpdateRequest;
use App\Http\Resources\Api\OneC\Catalog\Tickets\TicketResource;
use App\Models\Catalog\Tickets\Ticket;
use App\Permissions\Catalog\Products\UpdatePermission;
use App\Services\Catalog\Tickets\TicketService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @group Tickets
 *
 * @enum App\Enums\Tickets\TicketStatusEnum
 */
class TicketController extends Controller
{
    /**
     * Exists
     *
     * @response {
     *  "exists" => "boolean"
     * }
     */
    public function exists(string $guid): JsonResponse
    {
        return response()->json(
            [
                'exists' => Ticket::query()->where('guid', $guid)->exists(),
            ]
        );
    }

    /**
     * Update code
     *
     * Update ticket unique ticket identifier (code)
     *
     * @permission catalog.product.update
     *
     * @responseFile docs/api/tickets/single.json
     */
    public function updateCode(Ticket $ticket, TicketUpdateCodeRequest $request): TicketResource
    {
        $ticket->code = $request->get('code');
        $ticket->save();

        return TicketResource::make($ticket);
    }

    /**
     * Store
     *
     * @permission catalog.product.update
     *
     * @responseFile 201 docs/api/tickets/single.json
     *
     * @throws Throwable
     */
    public function store(TicketCreateRequest $request, TicketService $service): JsonResponse
    {
        /** @var Ticket $ticket */
        $ticket = makeTransaction(
            static fn() => $service->create(
                $request->getDto()
            )
        );

        return TicketResource::make($ticket)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update
     *
     * @permission catalog.product.update
     *
     * @responseFile docs/api/tickets/single.json
     *
     * @throws Throwable
     */
    public function update(Ticket $ticket, TicketUpdateRequest $request, TicketService $service): TicketResource
    {
        /** @var Ticket $ticket */
        $ticket = makeTransaction(
            static fn() => $service->update(
                $ticket,
                $request->getDto(),
            )
        );

        return TicketResource::make($ticket);
    }

    /**
     * Destroy
     *
     * @permission catalog.product.update
     *
     * @response {
     * "success": true,
     * "message": "Ticket deleted"
     * }
     *
     * @throws AuthorizationException
     */
    public function destroy(Ticket $ticket, TicketService $service): JsonResponse
    {
        $this->authorize(UpdatePermission::KEY);

        $service->delete($ticket);

        return $this->success('Ticket deleted');
    }

    /**
     * Created by technicians
     *
     * @urlParam per_page int
     * @urlParam page int
     *
     * @responseFile docs/api/tickets/list.json
     */
    public function new(Request $request): AnonymousResourceCollection
    {
        return TicketResource::collection(
            Ticket::query()
                ->where('status', TicketStatusEnum::NEW)
                ->with('translations')
                ->paginate($request->get('per_page', 15))
        );
    }

    /**
     * Tickets without code
     *
     * @urlParam per_page int
     * @urlParam page int
     *
     * @responseFile docs/api/tickets/list.json
     */
    public function withoutCode(Request $request): AnonymousResourceCollection
    {
        return TicketResource::collection(
            Ticket::query()
                ->whereNull('code')
                ->with('translations')
                ->paginate($request->get('per_page', 15))
        );
    }
}
