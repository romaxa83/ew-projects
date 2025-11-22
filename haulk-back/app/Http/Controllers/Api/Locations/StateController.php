<?php


namespace App\Http\Controllers\Api\Locations;


use App\Events\ModelChanged;
use App\Http\Controllers\ApiController;
use App\Http\Requests\Locations\StateRequest;
use App\Http\Resources\Locations\StateResource;
use App\Models\Locations\State;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

class StateController extends ApiController
{

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/states",
     *     tags={"States"},
     *     summary="Get states paginated list",
     *     operationId="Get states data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="5"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="States per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field for sort",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="id",
     *              enum ={"id","name","status"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Type for sort",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Scope for filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="California",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Scope for filter by status",
     *          required=false,
     *          @OA\Schema(
     *              type="boolean",
     *              default="true",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StatePaginate")
     *     ),
     * )
     * @throws Exception
     */
    public function index(Request $request)
    {
        $this->authorize('locations');

        $orderBy = $request->input('order_by', 'id');
        $perPage = (int) $request->input('per_page', 10);
        $orderByType = $request->input('order_type', 'asc');
        $states = State::filter($request->all())->orderBy($orderBy, $orderByType)->paginate($perPage);
        return StateResource::collection($states);
    }

    /**
     * @param StateRequest $request
     * @return StateResource
     *
     * @OA\Post(
     *     path="/api/states",
     *     tags={"States"},
     *     summary="Create state",
     *     operationId="Create state",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="State name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="California",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Status of state",
     *          required=true,
     *          @OA\Schema(
     *              type="boolean",
     *              default="true",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/State")
     *     ),
     * )
     * @throws Throwable
     */
    public function store(StateRequest $request)
    {
        $this->authorize('locations create');

        $state = new State();
        $state->fill($request->all());
        $state->saveOrFail();
        event(new ModelChanged($state, 'Store State')); // Это кастомный ивент , чтоб не дергался стандартный нужно отключить трейт hasHistories в моделе
        return new StateResource($state);
    }

    /**
     * @param State $state
     * @return StateResource
     *
     * @OA\Get(
     *     path="/api/states/{stateId}",
     *     tags={"States"},
     *     summary="Get info about state",
     *     operationId="Get state data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/State")
     *     ),
     * )
     */
    public function show(State $state)
    {
        $this->authorize('locations read');

        return new StateResource($state);
    }

    /**
     * @param State $state
     * @param StateRequest $request
     * @return StateResource
     *
     * @OA\Put(
     *     path="/api/states/{stateId}",
     *     tags={"States"},
     *     summary="Update state",
     *     operationId="Update state",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="State id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="State name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="California",
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Status of state",
     *          required=true,
     *          @OA\Schema(
     *              type="boolean",
     *              default="true",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/State")
     *     ),
     * )
     */
    public function update(State $state, StateRequest $request)
    {
        $this->authorize('locations update');

        $state->fill($request->all());
        $state->update();
        return new StateResource($state);
    }

    public function destroy(int $id)
    {
        // TODO IF NEED
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     * @throws Exception
     *
     * @OA\Get(
     *     path="/api/states-list",
     *     tags={"States"},
     *     summary="Get states list without pagination",
     *     operationId="Get states data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="name",
     *          in="query",
     *          description="Scope for filter by name",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="California",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/StateList")
     *     ),
     * )
     */
    public function list(Request $request)
    {
        return StateResource::collection(
            self::getStatesList($request->only('name'))
        );
    }

    public static function getStatesList(array $params = [])
    {
        $data = State::filter($params)
            ->orderBy('country_code', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        $data->transform(function ($el) {
            unset($el['created_at'], $el['updated_at']);

            return $el;
        });

        return $data;
    }
}
