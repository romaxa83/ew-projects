@php
    /**
     * @var $name string
     * @var $list array
     * @var $selected array|string
     * @var $disableParent bool|false
     * @var $selectAttributes array
     * @var $optionsAttributes array
     */

    $selected = Form::getValueAttribute($name, $selected);

    if (array_get($selectAttributes, 'multiple', false) && !str_ends_with($name, '[]')) {
        $name = $name.'[]';
    }

    $selectAttributes = array_merge([
        'name' => $name,
        'id' => Form::getIdAttribute($name, $selectAttributes),
        'class' => array_merge(['form-control'], (array)array_get($selectAttributes, 'class', [])),
    ], $selectAttributes);
@endphp
<select {!! Html::attributes($selectAttributes) !!}>
    @if(!empty($selectAttributes['placeholder']))
        <option value="">{{ $selectAttributes['placeholder'] }}</option>
    @endif
    @foreach($list[null] ?? [] as $item)
        @include('cms-core::admin.components.option', ['item' => $item, 'offset' => 0])
    @endforeach
</select>
