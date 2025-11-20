<?php

namespace WezomCms\Core\Widgets;

use WezomCms\Core\Contracts\ButtonsContainerInterface;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class IndexButtons extends AbstractWidget
{
    /**
     * View name.
     *
     * @var string|null
     */
    protected $view = 'cms-core::admin.widgets.index-buttons';

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
                if(last(explode('/',$button->getLink())) === 'create'){
                    unset($buttons[$key]);
                }
            }
        }

        return compact('buttons');
    }
}
