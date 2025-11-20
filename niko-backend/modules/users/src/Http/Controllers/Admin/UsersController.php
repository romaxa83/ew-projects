<?php

namespace WezomCms\Users\Http\Controllers\Admin;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\MetaFields\Heading;
use WezomCms\Core\Settings\MetaFields\Title;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\PageName;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Users\Http\Requests\Admin\CreateUserRequest;
use WezomCms\Users\Http\Requests\Admin\UpdateUserRequest;
use WezomCms\Users\Models\User;

class UsersController extends AbstractCRUDController
{
    use SettingControllerTrait;
    use AjaxResponseStatusTrait;

    /**
     * Model name.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Base view path name.
     *
     * @var string
     */
    protected $view = 'cms-users::admin';

    /**
     * Resource route name.
     *
     * @var string
     */
    protected $routeName = 'admin.users';

    /**
     * Form request class name for "create" action.
     *
     * @var string
     */
    protected $createRequest = CreateUserRequest::class;

    /**
     * Form request class name for "update" action.
     *
     * @var string
     */
    protected $updateRequest = UpdateUserRequest::class;

    protected $hideCreateBnt = true;

    /**
     * Resource name for breadcrumbs and title.
     *
     * @return string
     */
    protected function title(): string
    {
        return __('cms-users::admin.Users');
    }

    /**
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function auth($id)
    {
        $user = User::findOrFail($id);

        Auth::guard('web')->login($user);

        return redirect()->route('cabinet');
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $users = $this->model()::search($request->get('term'));

        $results = [];
        if (!$request->get('page')) {
            $results[] = ['id' => '', 'text' => __('cms-core::admin.layout.Not set')];
        }
        foreach ($users as $user) {
            $results[] = ['id' => $user->id, 'text' => $user->fullname];
        }

        return $this->success([
            'results' => $results,
            'pagination' => [
                'more' => $users->hasMorePages(),
            ]
        ]);
    }

    /**
     * @param  User  $obj
     * @param  FormRequest  $request
     * @return array
     */
    protected function fillStoreData($obj, FormRequest $request): array
    {
        $obj->password = bcrypt($request->get('password'));

        return $request->except('password');
    }

    /**
     * @param  User  $obj
     * @param  FormRequest  $request
     * @return array
     */
    protected function fillUpdateData($obj, FormRequest $request): array
    {
        if ($password = $request->get('password')) {
            $obj->password = bcrypt($password);
        }

        return $request->except('password');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $result = [];

        $result[] = AdminLimit::make();

        return $result;
    }
}
