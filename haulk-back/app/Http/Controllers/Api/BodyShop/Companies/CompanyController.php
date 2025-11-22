<?php

namespace App\Http\Controllers\Api\BodyShop\Companies;

use App\Http\Controllers\ApiController;
use App\Http\Resources\BodyShop\Companies\CompanyListResource;
use App\Models\Saas\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompanyController extends ApiController
{
    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/companies",
     *     tags={"Companies Body Shop"},
     *     summary="Companies Body Shop",
     *     operationId="Companies Body Shop",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/CompaniesBSList"),
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('companies-bs');

        $companies = Company::query()
            ->where('use_in_body_shop', true)
            ->orderBy('name')
            ->get();

        return CompanyListResource::collection($companies);
    }
}
