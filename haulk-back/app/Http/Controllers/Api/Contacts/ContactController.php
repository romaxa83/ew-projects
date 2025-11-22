<?php

namespace App\Http\Controllers\Api\Contacts;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Contacts\ContactRequest;
use App\Http\Requests\Contacts\IndexContactRequest;
use App\Http\Resources\Contacts\ContactAutocompleteResource;
use App\Http\Resources\Contacts\ContactPaginatedResource;
use App\Http\Resources\Contacts\ContactResource;
use App\Http\Resources\Contacts\ContactTypesListResource;
use App\Models\Contacts\Contact;
use App\Services\Contacts\ContactService;
use Arr;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ContactController extends ApiController
{
    protected ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        parent::__construct();
        $this->contactService = $contactService;
        $this->contactService->setUser(authUser());
    }

    /**
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/contacts/types",
     *     tags={"Contacts"},
     *     summary="Get contact types list",
     *     operationId="Get contact types",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactTypesListResource")
     *     ),
     * )
     *
     */
    public function contactTypes(): AnonymousResourceCollection
    {
        return ContactTypesListResource::collection(
            $this->contactService->getContactTypesForContacts()
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param IndexContactRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/contacts",
     *     tags={"Contacts"},
     *     summary="Get contacts paginated list",
     *     operationId="Get contacts data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Contact name", required=false,
     *          @OA\Schema(type="string", default="Contact name")
     *     ),
     *     @OA\Parameter(name="type_id", in="query", description="Contact type", required=false,
     *          @OA\Schema(type="integer", default="")
     *     ),
     *     @OA\Parameter(name="page", in="query", description="Page number", required=false,
     *          @OA\Schema(type="integer", default="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", description="Contacts per page", required=false,
     *          @OA\Schema(type="integer", default="10")
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field to sort by", required=false,
     *          @OA\Schema(type="string", default="id", enum ={"id","full_name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Sort order", required=false,
     *          @OA\Schema(type="string", default="asc",enum ={"asc","desc"})
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactPaginatedResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $contacts = Contact::filter(Arr::only($validated, ['name', 'type_id']))
            ->orderBy($validated['order_by'], $validated['order_type'])
            ->oldest('id')
            ->paginate($validated['per_page']);

        return ContactPaginatedResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ContactRequest $request
     * @return ContactResource
     *
     * @OA\Post(
     *     path="/api/contacts",
     *     tags={"Contacts"},
     *     summary="Create contact",
     *     operationId="Create contact",
     *     deprecated=false,
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/ContactRequest", schema="ContactRequestStore")
     *          ),
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function store(ContactRequest $request): ContactResource
    {
        $this->authorize('create', Contact::class);

        $contact = $this->contactService->create($request->toDto());

        return ContactResource::make($contact);
    }

    /**
     * Display the specified resource.
     *
     * @param Contact $contact
     * @return ContactResource
     *
     * @OA\Get(
     *     path="/api/contacts/{contactId}",
     *     tags={"Contacts"},
     *     summary="Get contact info",
     *     operationId="Get contact data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function show(Contact $contact): ContactResource
    {
        $this->authorize('view', $contact);

        return ContactResource::make($contact);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ContactRequest $request
     * @param Contact $contact
     * @return ContactResource
     *
     * @OA\Put(
     *     path="/api/contacts/{contactId}",
     *     tags={"Contacts"},
     *     summary="Update contact",
     *     operationId="Update contact",
     *     deprecated=false,
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="appliaction/json",
     *              schema=@OA\Schema(ref="#/components/schemas/ContactRequest", schema="ContactRequestUpdate")
     *          ),
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Contact id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(ContactRequest $request, Contact $contact): ContactResource
    {
        $this->authorize('update', $contact);

        $contact = $this->contactService->update($contact, $request->toDto());

        return new ContactResource($contact);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Contact $contact
     * @return JsonResponse
     *
     * @throws AuthorizationException
     * @throws Exception
     * @OA\Delete(
     *     path="/api/contacts/{contactId}",
     *     tags={"Contacts"},
     *     summary="Delete contact",
     *     operationId="Delete contact",
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
    public function destroy(Contact $contact): JsonResponse
    {
        $this->authorize('delete', $contact);

        $this->contactService->delete($contact);

        return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Search contacts by name for autocomplete.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/contacts/search",
     *     tags={"Contacts"},
     *     summary="Get contacts for autocomplete",
     *     operationId="Get contacts data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="s",
     *          in="query",
     *          description="Contact name",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              default="Contact"
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ContactAutocompleteResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewList', Contact::class);

        $contacts = Contact::filter($request->only(['s']))->get();

        return ContactAutocompleteResource::collection($contacts);
    }
}
