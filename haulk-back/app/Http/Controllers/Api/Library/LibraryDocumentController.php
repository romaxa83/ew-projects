<?php

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Library\DocumentRequest;
use App\Http\Requests\Library\DocumentFilterRequest;
use App\Http\Requests\Library\DocumentSearchRequest;
use App\Http\Resources\Library\LibraryDocumentPaginatedResource;
use App\Http\Resources\Library\LibraryDocumentResource;
use App\Models\Library\LibraryDocument;
use App\Services\Events\EventService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Spatie\MediaLibrary\Models\Media;

class LibraryDocumentController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LibraryDocumentPaginatedResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/library",
     *     tags={"Library"},
     *     summary="Get documents paginated list",
     *     operationId="Get documents from library",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
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
     *          name="name",
     *          in="query",
     *          description="",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default=""
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
    public function index(DocumentFilterRequest $request): LibraryDocumentPaginatedResource
    {
        $this->authorize('viewList', LibraryDocument::class);

        $documentsQuery = LibraryDocument::filter($request->validatedForFilter())
            ->with(['user', 'owner'])
            ->orderBy($request->order_by, $request->order_type);

        $documents = $documentsQuery->paginate($request->per_page);

        return new LibraryDocumentPaginatedResource($documents);
    }

    /**
     * Display a listing of the resource.
     *
     * @param string $policy
     * @param DocumentFilterRequest $request
     * @return LibraryDocumentPaginatedResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/library/documents/{policy}",
     *     tags={"Library"},
     *     summary="Get documents paginated list",
     *     operationId="Get documents from library",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
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
     *          name="name",
     *          in="query",
     *          description="",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default=""
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="driver_id",
     *          in="query",
     *          description="",
     *          required=false,
     *          @OA\Schema(
     *              type="integer",
     *              default=""
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
     */
    public function documentsList(string $policy, DocumentFilterRequest $request): LibraryDocumentPaginatedResource
    {
        $this->authorize('viewList', LibraryDocument::class);

        $documentsQuery = LibraryDocument::filter($request->validatedForFilter())
            ->with(['user', 'owner']);

        $documentsQuery = $this->getDocumentsQueryAccordingRoleAndPolicy($documentsQuery, $policy);

        $documentsQuery
            ->filter($request->validatedForFilter())
            ->orderBy($request->order_by, $request->order_type);
        $documents = $documentsQuery->paginate($request->per_page);

        return new LibraryDocumentPaginatedResource($documents);
    }

    private function getDocumentsQueryAccordingRoleAndPolicy($query, $policy, $search = null)
    {
        if ($policy === 'public') {
            return $query->public();
        }

        if ($policy === 'private' && Auth::user()->isAdmin()) {
            return $query->private();
        }

        if ($policy === 'private' && !Auth::user()->isAdmin()) {
            if (!empty($search)) {
                return $query->onlyMyPrivate(Auth::user()->id)
                    ->orMyDriversPrivateSearch(Auth::user()->belongsToMe()->get('id'), $search);
            }
            return $query->onlyMyPrivate(Auth::user()->id)
                ->orMyDriversPrivate(Auth::user()->belongsToMe()->get('id'));
        }
    }

    /**
     * @param DocumentRequest $request
     * @return LibraryDocumentResource
     * @throws AuthorizationException
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @OA\Post(
     *     path="/api/library",
     *     tags={"Library"},
     *     summary="Upload document to the library",
     *     operationId="Upload document to the library",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
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
     *                  @OA\Property(
     *                      property="user_id",
     *                      type="integer",
     *                      description="Permissions for the user"
     *                  )
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
    public function store(DocumentRequest $request): LibraryDocumentResource
    {
        $this->authorize('create', LibraryDocument::class);

        $documentName = $this->sanitizeDocumentName(
            $request->file('document')->getClientOriginalName()
        );

        $document = new LibraryDocument();
        $document->owner_id = Auth::user()->id;

        if ($request->input('is_for_all_drivers')) {
            $document->user_id = null;
            $document->match_policy = 0;
        } else {
            $document->user_id = $request->input('user_id');
            $document->match_policy = 1;
        }

        $document->name = $documentName;
        $document->save();

        $document->addMedia($request->file('document'))
            ->usingFileName($documentName)
            ->toMediaCollection(LibraryDocument::MEDIA_COLLECTION_NAME)->save();

        EventService::library($document)
            ->user($request->user())
            ->create()
            ->broadcast();

        return LibraryDocumentResource::make($document);
    }


    private function sanitizeDocumentName(string $documentName): string
    {
        return strtolower(str_replace(['#', '/', '\\', ' '], '-', $documentName));
    }

    /**
     * Display the specified resource.
     *
     * @param LibraryDocument $library
     * @return JsonResponse|Media|null
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/library/{libraryId}",
     *     tags={"Library"},
     *     summary="Get document",
     *     operationId="Get document",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function show(LibraryDocument $library)
    {
        if (empty($library)) {
            return $this->makeErrorResponse(
                trans('library.document_not_found'),
                Response::HTTP_NOT_FOUND
            );
        }

        $this->authorize('view', $library);

        return $library->getFirstMedia(LibraryDocument::MEDIA_COLLECTION_NAME);
    }


    /**
     * @param LibraryDocument $library
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws \Exception
     * @OA\Delete(
     *     path="/api/library/{libraryId}",
     *     tags={"Library"},
     *     summary="Delete document from the library",
     *     operationId="Delete document from the library",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     */
    public function destroy(LibraryDocument $library)
    {
        $this->authorize('delete', $library);

        $library->clearMediaCollection(LibraryDocument::MEDIA_COLLECTION_NAME);
        $library->delete();

        EventService::library($library)
            ->user(\request()->user())
            ->delete()
            ->broadcast();

        return $this->makeSuccessResponse(trans('document_has_been_deleted'), Response::HTTP_NO_CONTENT);
    }

    /**
     * Search contacts by name for autocomplete.
     *
     * @param DocumentSearchRequest $request
     * @return LibraryDocumentPaginatedResource
     *
     * @throws AuthorizationException
     * @OA\Get(
     *     path="/api/library/documents/search",
     *     tags={"Library"},
     *     summary="Get documents for autocomplete",
     *     operationId="Get library data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Document name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Library"
     *          )
     *     ),
     *    @OA\Parameter(
     *          name="policy",
     *          in="query",
     *          description="Document name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Library",
     *              enum={"private","public"}
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
    public function search(DocumentSearchRequest $request)
    {
        $this->authorize('viewList', LibraryDocument::class);

        $documentsQuery = LibraryDocument::filter($request->only(['s']))
            ->with(['user', 'owner']);

        $documentsQuery = $this->getDocumentsQueryAccordingRoleAndPolicy($documentsQuery, $request->policy, $request->s);
        $documents = $documentsQuery->paginate(10);


        return new LibraryDocumentPaginatedResource($documents);
    }
}
