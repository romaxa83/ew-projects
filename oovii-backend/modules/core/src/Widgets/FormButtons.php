<?php

namespace WezomCms\Core\Widgets;

use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class FormButtons extends AbstractWidget
{
    /**
     * View name.
     *
     * @var string|null
     */
    protected $view = 'cms-core::admin.widgets.form-buttons';

    /**
     * @param  ButtonsContainerInterface  $container
     * @return array|null
     */
    public function execute(ButtonsContainerInterface $container): ?array
    {
        $buttons = $container->sort()->get();

        if (!$buttons || $buttons->isEmpty()) {
            return null;
        }

        if(isset($this->data['btnCreateHide']) && $this->data['btnCreateHide']){
            foreach ($buttons as $key => $button){
                if(isset($button->getAttributes()['data-action']) && $button->getAttributes()['data-action'] == 'save-and-create'){
                    unset($buttons[$key]);
                }
            }
        }

        return compact('buttons');
    }
}
