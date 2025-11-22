<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OneC\Catalog\Certificates\CertificateResource;
use App\Models\Catalog\Certificates\Certificate;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Certificates
 */
class CertificatesController extends Controller
{
    /**
     * List
     *
     * @bodyParam page int
     * @bodyParam per_page int
     *
     * @responseFile docs/api/certificates/list.json
     */
    public function index(): AnonymousResourceCollection
    {
        return CertificateResource::collection(
            Certificate::query()
                ->with('type')
                ->paginate()
        );
    }
}
