<?php

namespace WezomCms\AmoCrm;

use Event;
use WezomCms\AmoCrm\Commands\AccountCommand;
use WezomCms\AmoCrm\Commands\AmoConnectCommand;
use WezomCms\AmoCrm\Listeners\AmoSubmitOrderToCrmListener;
use WezomCms\Core\BaseServiceProvider;
use WezomCms\Core\Contracts\PermissionsContainerInterface;
use WezomCms\Core\Traits\SidebarMenuGroupsTrait;
use WezomCms\Orders\Events\CreatedOrders;

class AmoCrmServiceProvider extends BaseServiceProvider
{
    use SidebarMenuGroupsTrait;

    public function registerCommands()
    {
        $this->commands([
            AmoConnectCommand::class,
            AccountCommand::class,
        ]);
    }

    /**
     * Register module listeners.
     */
    protected function registerListeners()
    {
        parent::registerListeners();
        if (config('cms.amo-crm.amo-crm.sub_domain')){
            Event::listen(CreatedOrders::class, AmoSubmitOrderToCrmListener::class);
        }
//        Event::listen(AutoPayedOrder::class, UpdateOrderInCrmListener::class);
    }

    /**
     * @param  PermissionsContainerInterface  $permissions
     */
    public function permissions(PermissionsContainerInterface $permissions)
    {
        $permissions->editSettings('amo-crm', __('cms-amo-crm::admin.Edit amoCRM settings'));
    }

    public function adminMenu()
    {
        $this->serviceGroup()
            ->add(__('cms-amo-crm::admin.amoCRM'), ['route' => 'admin.amo-crm.settings'])
            ->data('icon', 'fa-plug')
            ->data('position', 1009)
            ->data('permission', 'amo-crm.edit-settings')
            ->nickname('amo-crm-settings');
    }
}
