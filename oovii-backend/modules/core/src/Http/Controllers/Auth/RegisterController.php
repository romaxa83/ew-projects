<?php

namespace WezomCms\Core\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Notification;
use WezomCms\Core\Http\Controllers\AdminController;
use WezomCms\Core\Http\Controllers\RedirectsUsers;
use WezomCms\Core\Http\Requests\Auth\RegisterRequest;
use WezomCms\Core\Models\Administrator;
use WezomCms\Orders\Services\SdekService;
use WezomCms\Providers\Dto\ProviderDto;
use WezomCms\Providers\Notifications\NewProviderNotification;
use WezomCms\Providers\Services\ProviderService;

class RegisterController extends AdminController
{
    use RedirectsUsers;

    public function __construct(
        protected ProviderService $providerService,
        private SdekService $sdekService
    )
    {
        parent::__construct();
    }

    public function showRegisterForm()
    {
        $this->pageName->setPageName(__('cms-core::admin.auth.Register'));

        $this->renderJsValidator(RegisterRequest::class);

        return view('cms-core::admin.auth.register', [
            'regions' => $this->sdekService->getRegionsForSelect(),
        ]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $dto = ProviderDto::byRegistry($request->all());
        $model = $this->providerService->create($dto);

        $administrators = Administrator::toNotifications('providers.edit')->get();
        Notification::send($administrators, new NewProviderNotification($model));

        flash(__('cms-core::admin.layout.Register request created'))->success();

        return redirect()->intended(route('admin.login'));
    }
}

