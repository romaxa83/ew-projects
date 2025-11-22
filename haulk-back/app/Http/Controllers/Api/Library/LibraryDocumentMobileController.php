<?php

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Library\DocumentRequest;
use App\Http\Resources\Library\LibraryDocumentPaginatedResource;
use App\Http\Resources\Library\LibraryDocumentResource;
use App\Models\Library\LibraryDocument;
use App\Services\Events\EventService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;

class LibraryDocumentMobileController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LibraryDocumentPaginatedResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/mobile/library",
     *     tags={"Library"},
     *     summary="Get documents paginated list",
     *     operationId="Get documents from library",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="date_from",
     *          in="query",
     *          description="Date from filtering (YYYY-MM-DD)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="date_to",
     *          in="query",
     *          description="Date to filtering (YYYY-MM-DD)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="policy",
     *          in="query",
     *          description="Policy to filtering policy public or private",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="public",
     *              enum ={"public","private"}
     *          )
     *     ),
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
     *          description="Orders per page",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default="10"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Field to sort by",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="created_at",
     *              enum ={"created_at","name"}
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="order_type",
     *          in="query",
     *          description="Sort order",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="asc",
     *              enum ={"asc","desc"}
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/LibraryDocumentPaginatedResource")
     *     ),
     * )
     *
     */
    public function index(Request $request): LibraryDocumentPaginatedResource
    {
        $this->authorize('viewList', LibraryDocument::class);

        $orderBy = in_array($request->input('order_by'), ['created_at', 'name']) ? $request->input(
            'order_by'
        ) : 'created_at';
        $orderByType = in_array($request->input('order_type'), ['asc', 'desc']) ? $request->input(
            'order_type'
        ) : 'desc';
        $perPage = (int) $request->input('per_page', 10);

        $policy = $request->input('policy');

        $documentsQuery = LibraryDocument::where(function ($query) use ($policy) {
            if (!isset($policy)) {
                $query->where('match_policy', 0)
                    ->orWhere('user_id', Auth::user()->id)
                    ->orWhere('owner_id', Auth::user()->id);
            } elseif ($policy === 'public') {
                $query->where('match_policy', 0);
            } elseif ($policy === 'private') {
                $query->where('match_policy', 1)
                    ->where(function ($que) {
                        $que->orWhere('user_id', Auth::user()->id)
                            ->orWhere('owner_id', Auth::user()->id);
                    });
            }
        })
            ->with(['user', 'owner'])
            ->filter($request->only(['date_from', 'date_to']))
            ->orderBy($orderBy, $orderByType);

        $documents = $documentsQuery->paginate($perPage);
        return new LibraryDocumentPaginatedResource($documents);
    }

    /**
     * @param DocumentRequest $request
     * @return LibraryDocumentResource
     * @throws AuthorizationException
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @OA\Post(
     *     path="/api/mobile/library",
     *     tags={"Library"},
     *     summary="Upload document to the library",
     *     operationId="Upload document to the library",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="document",
     *                      type="string",
     *                      format="binary",
     *                  ),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/LibraryDocumentResource")
     *     ),
     * )
     */
    public function store(DocumentRequest $request)
    {
        $this->authorize('create', LibraryDocument::class);

        $documentName = $this->sanitizeDocumentName(
            $request->file('document')->getClientOriginalName()
        );

        $document = new LibraryDocument();
        $document->owner_id = Auth::user()->id;
        $document->user_id = Auth::user()->id;
        $document->match_policy = 1;
        $document->name = $documentName;
        $document->save();

        $document->addMedia($request->file('document'))
            ->usingFileName($documentName)
            ->toMediaCollection(LibraryDocument::MEDIA_COLLECTION_NAME)->save();

        EventService::library($document)
            ->user($request->user())
            ->create(true)
            ->broadcast();

        return LibraryDocumentResource::make($document);
    }

    private function sanitizeDocumentName(string $documentName): string
    {
        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $documentName));
    }
}
