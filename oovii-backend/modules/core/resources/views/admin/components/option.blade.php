@php
    /**
     * @var $name string
     * @var $list array
     * @var $selected array|string
     * @var $disableParent bool|false
     * @var $selectAttributes array
     * @var $optionsAttributes array
     * @var $item array
     * @var $offset int
     */

    $hasChildren = !empty($list[$item['value']]);

    $option = Form::getSelectOption(
        str_repeat('&nbsp;', $offset * 4) . $item['name'],
        $item['value'],
        $selected,
        $hasChildren ? array_merge(['disabled' => $disableParent], $optionsAttributes) : $optionsAttributes
    );
@endphp
{!! $option !!}
@if($hasChildren)
    @foreach($list[$item['value']] as $subItem)
        @include('cms-core::admin.components.option', ['item' => $subItem, 'offset' => $offset + 1])
    @endforeach
@endif
