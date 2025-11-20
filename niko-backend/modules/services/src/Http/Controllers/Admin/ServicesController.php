<?php

namespace WezomCms\Services\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\MetaFields\SeoFields;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\SiteLimit;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Services\Http\Requests\Admin\ServiceRequest;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Models\ServiceGroup;

class ServicesController extends AbstractCRUDController
{

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-services::admin';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.services';

    /**
     * Form request class name.
     *
     * @var string
     */
    protected $request = ServiceRequest::class;

    /**
     * @var bool
     */
    private $useGroups;

    /**
     * ServicesController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->useGroups = (bool)config('cms.services.services.use_groups');
    }

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-services::admin.Services');
    }

    /**
     * @param  Builder  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->orderBy('sort');
    }

    /**
     * @param  Service  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function formData($obj, array $viewData): array
    {
        $groups = [];

        if ($this->useGroups) {
            $groups = ServiceGroup::getForSelect();
        }

        return [
            'groups' => $groups,
        ];
    }

    /**
     * @param  Service  $obj
     * @param  FormRequest  $request
     * @return array
     */
    protected function fill($obj, FormRequest $request): array
    {
        $data = parent::fill($obj, $request);

        if (!$this->useGroups && isset($data['service_group_id'])) {
            unset($data['service_group_id']);
        }

        return $data;
    }
}
