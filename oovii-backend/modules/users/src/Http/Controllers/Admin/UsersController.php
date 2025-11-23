<?php

namespace WezomCms\Users\Http\Controllers\Admin;

use Auth;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use WezomCms\Core\Http\Controllers\AbstractCRUDController;
use WezomCms\Core\Settings\AdminLimit;
use WezomCms\Core\Settings\Fields\AbstractField;
use WezomCms\Core\Settings\Fields\Input;
use WezomCms\Core\Settings\MultilingualGroup;
use WezomCms\Core\Settings\PageName;
use WezomCms\Core\Settings\RenderSettings;
use WezomCms\Core\Settings\Tab;
use WezomCms\Core\Traits\AjaxResponseStatusTrait;
use WezomCms\Core\Traits\SettingControllerTrait;
use WezomCms\Users\Exports\UserExport;
use WezomCms\Users\Http\Requests\Admin\CreateUserRequest;
use WezomCms\Users\Http\Requests\Admin\UpdateUserRequest;
use WezomCms\Users\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use WezomCms\Users\Repositories\UserRepository;

class UsersController extends AbstractCRUDController
{
    use SettingControllerTrait;
    use AjaxResponseStatusTrait;

    protected $model = User::class;

    protected $view = 'cms-users::admin';

    protected $routeName = 'admin.users';

    protected ?string $exportUrl = 'admin.users.export';

    protected $createRequest = CreateUserRequest::class;

    protected $updateRequest = UpdateUserRequest::class;

    public function __construct(
        protected UserRepository $repo
    )
    {
        parent::__construct();
    }

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
        /** @var Collection|User[] $users */
        $users = $this->model()::search($request->get('term'));

        $results = [];
        if (!$request->get('page')) {
            $results[] = ['id' => '', 'text' => __('cms-core::admin.layout.Not set')];
        }
        foreach ($users as $user) {
            $results[] = ['id' => $user->id, 'text' => $user->full_name];
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

        $this->fillEmailVerified($obj, $request);

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

        $this->fillEmailVerified($obj, $request);

        return $request->except('password');
    }

    /**
     * @return array|AbstractField[]|MultilingualGroup[]
     * @throws \Exception
     */
    protected function settings(): array
    {
        $result = [];

        $result[] = MultilingualGroup::make(
            new RenderSettings(new Tab('cabinet', __('cms-users::admin.Cabinet'), 1, 'fa-file-text')),
            [PageName::make()->default('Cabinet')]
        );

        // $this->addSocialSettings($result);
        $this->addSocialLinksSettings($result);

        $this->addSmsSenderSettings($result);

        $result[] = AdminLimit::make();

        return $result;
    }

    /**
     * @param  User  $obj
     * @param  Request  $request
     */
    private function fillEmailVerified($obj, Request $request)
    {
        if ($request->get('email_verified', false)) {
            if (!$obj->hasVerifiedEmail()) {
                $obj->email_verified_at = Date::now();
                $obj->temporary_code = null;
            }
        } else {
            $obj->email_verified_at = null;
        }
    }

    /**
     * @param array $result
     * @throws Exception
     */
    private function addSocialLinksSettings(array &$result): void
    {
        $supportedSocialLinks = config('cms.users.users.supported_social_links');

        if (!$supportedSocialLinks) {
            return;
        }

        $socials = new RenderSettings(new Tab('social_links', __('cms-users::admin.Social links'), 4, 'fa-key'));

        foreach ($supportedSocialLinks as $index => $socialLink) {
            $result[] = Input::make($socials)
                ->setKey($socialLink . '_link')
                ->setName(__('cms-users::admin.social_links.' . $socialLink))
                ->setRules('nullable|string|max:255')
                ->setSort($index);
        }
    }

    /**
     * @param  array  $result
     * @throws \Exception
     */
    private function addSocialSettings(array &$result)
    {
        $supportedSocials = config('cms.users.users.supported_socials');

        if (!$supportedSocials) {
            return;
        }

        $socials = new RenderSettings(new Tab('socials', __('cms-users::admin.Socials'), 4, 'fa-key'));

        $index = 0;
        if (in_array('facebook', $supportedSocials)) {
            $result[] = Input::make($socials)
                ->setKey('facebook_id')
                ->setName(__('cms-users::admin.Facebook ID'))
                ->setHelpText(__('cms-users::admin.Application id'))
                ->setRules('nullable|string|max:255')
                ->setSort($index++);
            $result[] = Input::make($socials)
                ->setKey('facebook_secret_key')
                ->setName(__('cms-users::admin.Facebook secret key'))
                ->setHelpText(__('cms-users::admin.Application secret'))
                ->setSmallText(
                    sprintf(
                        '%s <span class="text-primary">%s</span>',
                        __('cms-users::admin.Facebook Redirect URI:'),
                        secure_url(route('auth.socialite.callback', 'facebook', false))
                    )
                )
                ->setRules('nullable|string|max:255')
                ->setSort($index++);
        }

        if (in_array('google', $supportedSocials)) {
            $result[] = Input::make($socials)
                ->setKey('google_id')
                ->setName(__('cms-users::admin.Google ID'))
                ->setRules('nullable|string|max:255')
                ->setSort($index++);
            $result[] = Input::make($socials)
                ->setKey('google_secret_key')
                ->setName(__('cms-users::admin.Google secret key'))
                ->setSmallText(
                    sprintf(
                        '%s <span class="text-primary">%s</span>',
                        __('cms-users::admin.Google Redirect URI:'),
                        secure_url(route('auth.socialite.callback', 'google', false))
                    )
                )
                ->setRules('nullable|string|max:255')
                ->setSort($index++);
        }

        if (in_array('twitter', $supportedSocials)) {
            $result[] = Input::make($socials)
                ->setKey('twitter_id')
                ->setName(__('cms-users::admin.Twitter ID'))
                ->setRules('nullable|string|max:255')
                ->setSort($index++);
            $result[] = Input::make($socials)
                ->setKey('twitter_secret_key')
                ->setName(__('cms-users::admin.Twitter secret key'))
                ->setSmallText(
                    sprintf(
                        '%s <span class="text-primary">%s</span>',
                        __('cms-users::admin.Twitter Redirect URI:'),
                        secure_url(route('auth.socialite.callback', 'twitter', false))
                    )
                )
                ->setRules('nullable|string|max:255')
                ->setSort($index);
        }
    }

    /**
     * @param  array  $result
     * @throws \Exception
     */
    private function addSmsSenderSettings(array &$result)
    {
        $rs = new RenderSettings(new Tab('sms_service', __('cms-users::admin.SMS service'), 4));

        switch (config('cms.sms-verify.config.sender.driver')) {
            case 'esputnik':
                $result[] = Input::make($rs)
                    ->setKey('user')
                    ->setName(__('cms-users::admin.Esputnik User'))
                    ->setRules('nullable|string');
                $result[] = Input::make($rs)
                    ->setKey('password')
                    ->setName(__('cms-users::admin.Esputnik Password'))
                    ->setRules('nullable|string');
                $result[] = Input::make($rs)
                    ->setKey('from')
                    ->setName(__('cms-users::admin.Esputnik SMS sender name'))
                    ->default('marketing')
                    ->setRules('nullable|string');
                break;
            case 'turbosms':
                $result[] = Input::make($rs)
                    ->setKey('login')
                    ->setName(__('cms-users::admin.TurboSMS Login'))
                    ->setRules('nullable|string');
                $result[] = Input::make($rs)
                    ->setKey('secret')
                    ->setName(__('cms-users::admin.TurboSMS Secret'))
                    ->setRules('nullable|string');
                $result[] = Input::make($rs)
                    ->setKey('sender')
                    ->setName(__('cms-users::admin.TurboSMS sender name'))
                    ->default('Msg')
                    ->setRules('nullable|string');
                break;
            case 'kazinfoteh':
                $result[] = Input::make($rs)
                    ->setKey('kazinfoteh_login')
                    ->setName(__('cms-users::admin.sms-drivers.kazinfoteh.Login'))
                    ->setRules('nullable|string');
                $result[] = Input::make($rs)
                    ->setKey('kazinfoteh_password')
                    ->setName(__('cms-users::admin.sms-drivers.kazinfoteh.Password'))
                    ->setRules('nullable|string');
                break;
        }
    }

    public function export(Request $request)
    {
        try {
            return Excel::download(new UserExport($request, $this->repo), 'users.xlsx');
        } catch (Exception $e){
            report($e);
            flash($e->getMessage())->error();

            return redirect()->route($this->makeRouteName('index'));
        }
    }
}
