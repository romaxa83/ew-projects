@php
    $currentOptionAttributes = array_merge(['value' => $item['value'], 'selected' => $item['value'] == $selected], $optionsAttributes);
@endphp
@if(!empty($list[$item['value']]))
    <option {!! app('html')->attributes(array_merge(['disabled' => $disableParent], $currentOptionAttributes)) !!}
    >{!! str_repeat('&nbsp;', $offset * 4) !!}{{ $item['name'] }}</option>
    @foreach($list[$item['value']] as $subItem)
        @include('cms-core::admin.components.option', ['item' => $subItem, 'offset' => $offset + 1])
    @endforeach
@else
    <option {!! app('html')->attributes($currentOptionAttributes) !!}>{!! str_repeat('&nbsp;', $offset * 4) !!}{{ $item['name'] }}</option>
@endif
