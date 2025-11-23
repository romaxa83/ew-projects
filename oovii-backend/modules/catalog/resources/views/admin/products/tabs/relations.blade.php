@php
/**
 * @var $obj \WezomCms\Catalog\Models\Product
 */

@endphp
<div class="form-group">
    {!! Form::label('group_key', __('cms-catalog::admin.products.Group key')) !!}
    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top"
       title="@lang('cms-catalog::admin.products.Specify the key by which the goods will be combined')"></i>
    {!! Form::text('group_key') !!}
</div>
<div class="form-group">
    {!! Form::label('collections[]', __('cms-catalog::admin.collection.collections')) !!}
    <div class="input-group">

        {!! Form::select('collections[]', $collections, old('collections', $selectedCollections), ['multiple' => 'multiple', 'class' => 'js-select2']) !!}
    </div>
    <div class="input-group">

    </div>
</div>

<div class="form-group">
    {!! Form::label('relations[]',  __('cms-catalog::admin.products.relations')) !!}
    <select name="relations[]"
            data-template="product"
            multiple="multiple"
            class="js-import js-ajax-select2 form-control"
            data-url="{{ route('admin.products.search') }}"
    >

        @foreach($obj->relations ?? [] as $oneSelectOption)
            <option value="{{ $oneSelectOption->id }}"
                    @if($obj->relations->contains('id', $oneSelectOption->id)) selected="selected" @endif {!! Html::attributes([
                            'data-name' => $oneSelectOption->name,
                            'data-cost' => money($oneSelectOption->cost),
                            'data-currency' => money()->adminCurrencySymbol(),
                            'data-image' => $oneSelectOption->getImageUrl()]) !!}>{{ $oneSelectOption->name }}</option>
        @endforeach
    </select>
</div>

@foreach(event('admin:product-form', $obj) as $event)
    {!! $event !!}
@endforeach
