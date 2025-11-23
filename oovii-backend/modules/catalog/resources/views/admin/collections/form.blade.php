@php
 /**
 * @var \WezomCms\Catalog\Models\Collections\Collection $obj
 */
@endphp

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                @langTabs
                <div class="form-group">
                    {!! Form::label($locale . '[name]', __('cms-catalog::admin.brands.Name')) !!}
                    {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name), ['class' => ($loop->first ? 'slug-source' : '')]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label($locale . '[image]', __('cms-catalog::admin.collection.image')) !!}
                    {!! Form::imageUploader($locale . '[image]', $obj) !!}
                </div>
                @endLangTabs
                <div class="form-group">
                    {!! Form::label('products[]',  __('cms-catalog::admin.products.Products')) !!}
                    <select name="products[]"
                            data-template="product"
                            multiple="multiple"
                            class="js-import js-ajax-select2 form-control add-product-to-collection"
                            data-url="{{ route('admin.products.search') }}"
                    >
{{--                        @foreach($obj->products ?? [] as $oneSelectOption)--}}
{{--                            <option value="{{ $oneSelectOption->id }}"--}}
{{--                                    @if($obj->products->contains('id', $oneSelectOption->id))--}}
{{--                                        selected="selected"--}}
{{--                                @endif--}}
{{--                                {!! Html::attributes([--}}
{{--                                    'data-name' => $oneSelectOption->name,--}}
{{--                                    'data-cost' => money($oneSelectOption->cost),--}}
{{--                                    'data-qty' => __('cms-catalog::admin.qty', ['qty' => $oneSelectOption->amount]),--}}
{{--                                    'data-currency' => money()->adminCurrencySymbol(),--}}
{{--                                    'data-image' => $oneSelectOption->getImageUrl()--}}
{{--                                    ]) !!}--}}
{{--                            >--}}
{{--                                {{ $oneSelectOption->name }}--}}
{{--                            </option>--}}
{{--                        @endforeach--}}
                    </select>
                    <input class="hidden-product-list" type="hidden" name="products-list" value=" ">
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}--}}
{{--                    {!! Form::status('published') !!}--}}
{{--                </div>--}}
                <div class="form-group">
                    {!! Form::label('type', __('cms-catalog::admin.collection.type.title')) !!}
                    <div class="input-group">
                        {!! Form::select('type', $obj::typeList(), old('type', $obj->type), ['class' => 'js-select2']) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('moderator_id', __('cms-core::admin.moderator.moderator')) !!}
                    <div class="input-group">
                        {!! Form::select(
                            'moderator_id',
                            $moderators,
                            old('moderator_id', $obj->moderator_id),
                            ['class' => 'js-select2']
                        ) !!}
                    </div>
                </div>
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('category_id', __('cms-catalog::admin.collection.category.one')) !!}--}}
{{--                    <select name="category_id" id="category_id" class="form-control js-select2">--}}
{{--                        <option value="">@lang('cms-core::admin.layout.Not set')</option>--}}
{{--                        @foreach($categoriesTree as $key => $category)--}}
{{--                            <option value="{{ $key }}" {{ $key == old('category_id', $obj->category_id ?: request()->get('category_id')) ? 'selected': null }}--}}
{{--                                {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('start_at', __('cms-catalog::admin.collection.start_at')) !!}
                            {!! Form::text('start_at',
                                old('start_at', $obj->start_at != null ? $obj->start_at->format('d.m.Y H:i') : null),
                                [
                                    'class' => 'js-datetimepicker',
                                    'placeholder' => __('cms-catalog::admin.collection.start_at'),
                                ]
                            ) !!}
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-radio">
                                <input type="radio"
                                       class="custom-control-input"
                                       id="start_at_count"
                                       name="time_counter"
                                       value="{{ $obj::START_AT_COUNTER }}"
                                    {{ isset($obj->id) ? $obj->start_counter ? "checked" : null : "checked" }}
                                >
                                <label class="custom-control-label" for="start_at_count">
                                    {{__('cms-catalog::admin.time_counter')}}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('end_at', __('cms-catalog::admin.collection.end_at')) !!}
                            {!! Form::text('end_at',
                                old('end_at', $obj->end_at != null ? $obj->end_at->format('d.m.Y H:i') : null),
                                 [
                                     'class' => 'js-datetimepicker',
                                     'placeholder' => __('cms-catalog::admin.collection.end_at'),
                                 ]
                            ) !!}
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-radio">
                                <input type="radio"
                                       class="custom-control-input"
                                       id="end_at_count"
                                       name="time_counter"
                                       value="{{ $obj::END_AT_COUNTER }}"
                                       {{ isset($obj->id) ? $obj->end_counter ? "checked" : null : null }}
                                >
                                <label class="custom-control-label" for="end_at_count">
                                    {{__('cms-catalog::admin.time_counter')}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="py-2">
                    <strong>
                        @lang('cms-catalog::admin.collection.product_list')
{{--                        ( <span class="product-count">{{$obj->products->count()}}</span> )--}}
                    </strong>
                </h5>
            </div>
            <div class="card-body">
                <div class="product-list" data-collection-id="{{ $obj?->id }}">
                    @foreach($obj->products as $product)
                        @include('cms-catalog::admin.collections.product-list')
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let elem = document
        .getElementsByClassName('add-product-to-collection')
    ;
</script>
