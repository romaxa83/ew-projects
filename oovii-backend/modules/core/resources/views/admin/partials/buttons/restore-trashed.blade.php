@php
    /**
     * @var $button \WezomCms\Core\Foundation\Buttons\RestoreTrashed
     */
@endphp
@restoreResource(['obj' => $button->getModel(), 'ability' => $button->getAbility(), 'route' => $button->getRouteName(), 'formButton' => true])
