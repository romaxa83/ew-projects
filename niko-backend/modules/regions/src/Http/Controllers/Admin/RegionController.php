<?php

namespace WezomCms\Regions\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Regions\Http\Requests\Admin\RegionRequest;
use WezomCms\Regions\Models\Region;

class RegionController extends AbstractCRUDController
{
	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model = Region::class;

	/**
	 * Indicates whether to use pagination.
	 *
	 * @var bool
	 */
	protected $paginate = false;

	/**
	 * Base view path name.
	 *
	 * @var string
	 */
	protected $view = 'cms-regions::admin.region';

	/**
	 * Resource route name.
	 *
	 * @var string
	 */
	protected $routeName = 'admin.regions';

	/**
	 * Form request class name.
	 *
	 * @var string
	 */
	protected $request = RegionRequest::class;

	public function __construct()
	{
		parent::__construct();

	}

	/**
	 * Resource name for breadcrumbs and title.
	 *
	 * @return string
	 */
	protected function title(): string
	{
		return __('cms-regions::admin.Regions');
	}

	/**
	 * @param  Builder  $query
	 * @param  Request  $request
	 */
	protected function selectionIndexResult($query, Request $request)
	{
		$query->orderBy('sort');
	}
}

