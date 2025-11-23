<?php

namespace WezomCms\Core\Http\Controllers;

use Artesaos\SEOTools\Traits\SEOTools;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use WezomCms\Core\Traits\BreadcrumbsTrait;
use WezomCms\Core\Traits\LangSwitchingGenerator;

class SiteController extends Controller
{
    use AuthorizesRequests;
    use BreadcrumbsTrait;
    use DispatchesJobs;
    use LangSwitchingGenerator;
    use SEOTools;
    use ValidatesRequests;
}
