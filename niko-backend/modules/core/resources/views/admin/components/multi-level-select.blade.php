<select name="{{ $name }}"
        id="{{ app('form')->getIdAttribute($name, $selectAttributes) }}" {!! app('html')->attributes($selectAttributes) !!}>
    @if(!empty($selectAttributes['placeholder']))
        <option value="">{{ $selectAttributes['placeholder'] }}</option>
    @endif
    @foreach($list[null] ?? [] as $item)
        @include('cms-core::admin.components.option', ['item' => $item, 'offset' => 0])
    @endforeach
</select>
