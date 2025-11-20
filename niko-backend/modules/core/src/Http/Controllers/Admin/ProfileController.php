<?php

namespace WezomCms\Core\Http\Controllers\Admin;

use Auth;
use Cookie;
use Illuminate\Http\Request;
use JsValidator;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Models\Administrator;

class ProfileController extends AdminController
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit()
    {
        $this->pageName->setPageName(__('cms-core::admin.profile.Edit profile'));
        $this->addBreadcrumb(__('cms-core::admin.profile.Edit profile'));

        /** @var Administrator $user */
        $user = Auth::user();

        $this->assets->addInlineScript(JsValidator::make($this->getRules($user), [], [], '#form'));

        return view('cms-core::admin.auth.profile', ['user' => $user, 'apiEnabled' => $this->apiEnabled()]);
    }

    /**
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        /** @var Administrator $user */
        $user = Auth::user();

        $rules = $this->getRules($user);

        $password = $request->get('password');
        if ($password) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules);

        if ($this->apiEnabled()) {
            $user->api_token = $request->input('api_token');
        }

        $user->update($request->all(array_keys($rules)));

        // Update password
        if ($password) {
            $user->password = bcrypt($password);
            $user->save();
        }

        flash(__('cms-core::admin.layout.Data successfully updated'))->success();

        return redirect()->route('admin.edit-profile');
    }

    /**
     * @param $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeLocale($locale)
    {
        $locale = array_key_exists($locale, config('cms.core.translations.admin.locales', []))
            ? $locale
            : config('cms.core.translations.admin.default');

        Cookie::queue(Cookie::make('admin_locale', $locale));

        return redirect()->back();
    }

    /**
     * @param  Administrator  $user
     * @return array
     */
    private function getRules(Administrator $user)
    {
        $rules = [
            'name' => 'required|max:191',
            'email' => 'required|email|max:255|unique:administrators,email,' . $user->id,
            'notify' => 'required',
        ];

        if ($this->apiEnabled()) {
            $rules['api_token'] = 'nullable|string|max:80|unique:administrators,api_token,' . $user->id;
        }

        return $rules;
    }

    /**
     * @return bool
     */
    protected function apiEnabled(): bool
    {
        return Auth::user()->isSuperAdmin() && config('cms.core.api.enabled');
    }
}
