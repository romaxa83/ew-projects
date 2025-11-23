<?php

namespace WezomCms\Firebase;

use Illuminate\Contracts\Foundation\Application;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Firebase\Events\FcmGroupPush;
use WezomCms\Firebase\Events\FcmPush;
use WezomCms\Firebase\Listeners\FcmGroupPushListener;
use WezomCms\Firebase\Listeners\FcmPushListener;
use WezomCms\Firebase\Services\Sender\FirebaseSender;
use WezomCms\Firebase\Services\Sender\SimpleFirebaseSender;
use WezomCms\Core\Facades\SidebarMenu;

class FirebaseServiceProvider extends BaseServiceProvider
{
    protected $listen = [
        FcmPush::class => [
            FcmPushListener::class,
        ],
        FcmGroupPush::class => [
            FcmGroupPushListener::class,
        ],
    ];

    public function register(): void
    {
        $this->app->singleton(FirebaseSender::class, function (Application $app) {
            return new SimpleFirebaseSender(
                config('cms.firebase.firebase.firebase_sender_url'),
                config('cms.firebase.firebase.firebase_server_key'),

            );
        });
    }

    public function boot(): void
    {
        parent::boot();
    }

    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->add('fcm-templates', __('cms-firebase::admin.template.many'));
    }

    public function adminMenu()
    {
        SidebarMenu::add(__('cms-firebase::admin.template.many'), route('admin.fcm-templates.index'))
            ->data('permission', 'fcm-templates.view')
            ->data('icon', 'fa-paper-plane')
            ->data('position', 1)
            ->nickname('templates');

    }
}
