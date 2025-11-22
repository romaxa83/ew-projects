<?php

namespace App\Http\Controllers\Api\OneC\Technicians;

use App\Dto\Technicians\TechnicianDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Technicians\TechnicianCreateRequest;
use App\Http\Requests\Api\OneC\Technicians\TechniciansImportRequest;
use App\Http\Requests\Api\OneC\Technicians\TechniciansIndexRequest;
use App\Http\Requests\Api\OneC\Technicians\TechnicianUpdateRequest;
use App\Http\Resources\Api\OneC\Technicians\TechnicianResource;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianDeletePermission;
use App\Permissions\Technicians\TechnicianListPermission;
use App\Services\Technicians\TechnicianService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Throwable;

/**
 * @group Technicians
 */
class TechniciansController extends Controller
{
    /**
     * List
     *
     * @permission technician.technician.list
     *
     * @responseFile docs/api/technicians/list.json
     */
    public function index(TechniciansIndexRequest $request): AnonymousResourceCollection
    {
        return TechnicianResource::collection(
            Technician::query()
                ->select(['id', 'email', 'phone', 'guid'])
                ->paginate($request->get('per_page'))
        );
    }

    /**
     * New
     *
     * @permission technician.technician.list
     *
     * @responseFile docs/api/technicians/list.json
     */
    public function new(TechniciansIndexRequest $request): AnonymousResourceCollection
    {
        return TechnicianResource::collection(
            Technician::query()
                ->select(['id', 'email', 'phone', 'guid'])
                ->new()
                ->paginate($request->get('per_page'))
        );
    }

    /**
     * Show
     *
     * @permission technician.technician.list
     *
     * @responseFile docs/api/technicians/single.json
     * @throws AuthorizationException
     */
    public function show(Technician $technician): TechnicianResource
    {
        $this->authorize(TechnicianListPermission::KEY);

        return new TechnicianResource($technician);
    }

    /**
     * Update
     *
     * @permission technician.technician.update
     *
     * @responseFile docs/api/technicians/single.json
     *
     * @throws Throwable
     */
    public function update(
        Technician $technician,
        TechnicianUpdateRequest $request,
        TechnicianService $service
    ): TechnicianResource {
        return makeTransaction(
            static fn() => new TechnicianResource(
                $service->update(
                    $technician,
                    TechnicianDto::byArgs($request->validated()),
                )
            )
        );
    }

    /**
     * Destroy
     *
     * @permission technician.technician.delete
     *
     * @response {
     * "success": true,
     * "message": "User deleted"
     * }
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function destroy(Technician $technician, TechnicianService $service): JsonResponse
    {
        $this->authorize(TechnicianDeletePermission::KEY);

        $service->delete(
            (new Collection())->add($technician)
        );

        return $this->success('Technician deleted');
    }

    /**
     * Store
     *
     * @permission technician.technician.create
     *
     * @responseFile 201 docs/api/technicians/single.json
     *
     * @throws Throwable
     * @throws AuthorizationException
     */
    public function store(TechnicianCreateRequest $request, TechnicianService $service): TechnicianResource
    {
        return makeTransaction(
            static fn() => new TechnicianResource(
                $service->register(
                    TechnicianDto::byArgs($request->validated())
                )
            )
        );
    }

    /**
     * Import
     *
     * @permission technician.technician.create
     *
     * @responseFile 201 docs/api/technicians/list.json
     *
     * @throws Throwable
     */
    public function import(TechniciansImportRequest $request, TechnicianService $service): AnonymousResourceCollection
    {
        $response = [];

        foreach ($request->get('technicians') as $technicianData) {
            $response[] = makeTransaction(
                static function () use ($service, $technicianData) {
                    return $service->register(
                        TechnicianDto::byArgs($technicianData)
                    );
                }
            );
        }

        return TechnicianResource::collection($response);
    }
}
