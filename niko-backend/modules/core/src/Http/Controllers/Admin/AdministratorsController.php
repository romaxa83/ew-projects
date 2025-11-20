<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Http\Requests\Admin\CreateAdministratorRequest;
use WezomCms\Core\Http\Requests\Admin\UpdateAdministratorRequest;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;

class AdministratorsController extends AbstractCRUDController
{
    /**
     * Model name.
     *
     * @var string
     */
    protected $model = Administrator::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.auth.administrators';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.administrators';

    /**
     * Form request class name for "create" action.
     *
     * @var string
     */
    protected $createRequest = CreateAdministratorRequest::class;

    /**
     * Form request class name for "update" action.
     *
     * @var string
     */
    protected $updateRequest = UpdateAdministratorRequest::class;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-core::admin.administrators.Administrators');
    }

    /**
     * @param  Builder|Administrator  $query
     * @param  Request  $request
     */
    protected function selectionIndexResult($query, Request $request)
    {
        $query->notSuperAdmin()->with('roles');

        event('administrators.selection_index_result', compact('query', 'request'));
    }

    /**
     * @param  Administrator  $obj
     * @param  array  $viewData
     * @return array
     */
    protected function formData($obj, array $viewData): array
    {
        $viewData['roles'] = Role::orderBy('name')->get();
        $viewData['selectedRoles'] = $obj->roles()->pluck('roles.id')->toArray();
        $viewData['eventFields'] = event('administrators.render_form', compact('obj', 'viewData'));
        $viewData['renderLeftForm'] = event('administrators.render_left_form', compact('obj', 'viewData'));

        return $viewData;
    }

    /**
     * @param  Administrator  $obj
     * @param  FormRequest  $request
     * @return array
     */
    protected function fillStoreData($obj, FormRequest $request): array
    {
        $obj->active = $request->get('active');
        $obj->name = $request->get('name');
        $obj->email = $request->get('email');
        $obj->password = bcrypt($request->get('password'));

        return [];
    }

    /**
     * @param  Administrator  $obj
     * @param  Request  $request
     */
    protected function afterSuccessfulSave($obj, Request $request)
    {
        $obj->roles()->sync($request->get('ROLES', []));
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($id)
    {
        $this->model()::notSuperAdmin()->findOrFail($id);

        return parent::edit($id);
    }

    /**
     * @param  Request  $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, $id)
    {
        $this->model()::notSuperAdmin()->findOrFail($id);

        return parent::update($request, $id);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $this->model()::notSuperAdmin()->findOrFail($id);

        return parent::destroy($id);
    }

    /**
     * @param  Administrator  $obj
     * @param  FormRequest  $request
     * @return array
     */
    protected function fillUpdateData($obj, FormRequest $request): array
    {
        $obj->active = $request->get('active');
        $obj->name = $request->get('name');
        $obj->email = $request->get('email');

        $password = $request->get('password');
        if ($password) {
            $obj->password = bcrypt($request->get('password'));
        }

        return [];
    }

    /**
     * @param $ability
     * @param  array|null  $arguments
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeForAction($ability, ...$arguments)
    {
        $this->authorize('super_admin');
    }
}
