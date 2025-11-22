<?php

namespace App\Http\Controllers\Api\OneC\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OneC\Catalog\Video\VideoLinkResource;
use App\Models\Catalog\Videos\VideoLink;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Video Links
 */
class VideoLinkController extends Controller
{
    /**
     * List
     *
     * @bodyParam page int
     * @bodyParam per_page int
     * @responseFile docs/api/videos/list.json
     */
    public function index(): AnonymousResourceCollection
    {
        return VideoLinkResource::collection(
            VideoLink::query()
                ->with('translation')
                ->with('group.translation')
                ->latest('sort')
                ->paginate()
        );
    }
}
