@php
    $tabs = [
        __('cms-catalog::admin.products.Main data') => $viewPath . '.tabs.main-data',
        __('cms-catalog::admin.specifications.Specifications') => $viewPath . '.tabs.specifications',
        __('cms-catalog::admin.products.Relations') => $viewPath . '.tabs.relations',
        //__('cms-catalog::admin.products.SEO') => $viewPath . '.tabs.seo',
    ];

    // if (\WezomCms\Core\Foundation\Helpers::providerLoaded(\WezomCms\PromoCodes\PromoCodesServiceProvider::class)) {
    //    $tabs[__('cms-catalog::admin.Promo codes')] = $viewPath.'.tabs.promo-codes';
    // }
@endphp
<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
{{--                @if(empty($categoriesTree) && Gate::allows('categories.create'))--}}
{{--                    <div class="alert alert-danger">@lang('cms-catalog::admin.products.We recommend that you') <a href="{{ route('admin.categories.create') }}">@lang('cms-catalog::admin.products.create a category first')</a></div>--}}
{{--                @endif--}}
                @tabs($tabs)
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        @can('products.moderate', $obj)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="py-2">
                        <strong>@lang('cms-catalog::admin.For moderator')</strong>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('moderated', __('cms-catalog::admin.products.Moderate status')) !!}
                                {!! Form::status(
                                    'moderated',
                                    $obj->moderated,
                                    false,
                                    __('cms-catalog::admin.products.Moderated'),
                                    __('cms-catalog::admin.products.Not moderated'),
                                ) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            @if(!$obj->moderated)
                <div class="alert alert-danger">
                    @lang('cms-catalog::admin.products.Product is not moderated yet')
                </div>
            @endif
        @endcan
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
{{--                            {!! Form::status('published') !!}--}}
                            {!! Form::status('published', $obj->published, false, null, null, ['disabled' => !$obj->moderated]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('available', __('cms-catalog::admin.products.Are available')) !!}
                            {!! Form::status('available') !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('category_id', __('cms-catalog::admin.products.Category')) !!}
                            <select name="category_id" id="category_id" class="form-control js-select2">
                                <option value="">@lang('cms-core::admin.layout.Not set')</option>
                                @foreach($categoriesTree as $key => $category)
                                    <option value="{{ $key }}" {{ $key == old('category_id', $obj->category_id ?: request()->get('category_id')) ? 'selected': null }}
                                        {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('brand_id', __('cms-catalog::admin.products.Brand')) !!}
                            <div class="input-group">
                                {!! Form::select('brand_id', $brands, old('brand_id', $selectedBrand), ['class' => 'js-select2']) !!}
                            </div>
                        </div>
                    </div>
                </div>
{{--                @widget('catalog:brand-with-model', ['brand' => $obj->brandl])--}}
{{--                <div class="row">--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('novelty', __('cms-catalog::admin.products.Novelty')) !!}--}}
{{--                            {!! Form::status('novelty', null, false)  !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('popular', __('cms-catalog::admin.products.Popular')) !!}--}}
{{--                            {!! Form::status('popular', null, false) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-4">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('sale', __('cms-catalog::admin.products.Sale')) !!}--}}
{{--                            {!! Form::status('sale', null, false, null, null, ['class' => 'js-product-sale-toggle']) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('popular', __('cms-catalog::admin.products.Popular')) !!}
                            {!! Form::status('popular', null, false) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('best_price', __('cms-catalog::admin.products.Best price')) !!}
                            {!! Form::status('best_price', null, false) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('cost', __('cms-catalog::admin.products.Cost')) !!}
                            <div class="input-group">
                                {!! Form::number('cost', str_replace(',', '.', old('cost', $obj->cost)), ['min' => 0, 'step' => '1', 'class' => 'js-product-cost']) !!}
                                <div class="input-group-append"><span
                                        class="input-group-text">{{ money()->adminCurrencySymbol() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('cost_discount', __('cms-catalog::admin.products.cost discount')) !!}
                            <div class="input-group">
                                {!! Form::number('cost_discount', str_replace(',', '.', old('cost_discount', $obj->cost_discount)), ['min' => 0, 'step' => '1']) !!}
                                <div class="input-group-append"><span
                                        class="input-group-text">{{ money()->adminCurrencySymbol() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
{{--                    <div class="col-md-6">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('old_cost', __('cms-catalog::admin.products.Old cost')) !!}--}}
{{--                            <div class="input-group">--}}
{{--                                {!! Form::number('old_cost', str_replace(',', '.', old('old_cost', $obj->old_cost)), ['min' => 0, 'step' => '1', 'class' => 'js-product-old-cost']) !!}--}}
{{--                                    <div class="input-group-append"><span--}}
{{--                                                class="input-group-text">{{ money()->adminCurrencySymbol() }}</span>--}}
{{--                                    </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('bonus', __('cms-catalog::admin.products.Bonus')) !!}
                            <div class="input-group">
                                {!! Form::number('bonus', str_replace(',', '.', old('bonus', $obj->bonus)), ['min' => 0, 'step' => '1']) !!}
                                <div class="input-group-append"><span
                                        class="input-group-text">{{ money()->adminCurrencySymbol() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('amount', __('cms-catalog::admin.products.amount')) !!}
                            {!! Form::number('amount', old('amount', $obj->amount), ['min' => 0, 'step' => '1']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('amount_one_user', __('cms-catalog::admin.products.amount one user')) !!}
                            {!! Form::number('amount_one_user', old('amount_one_user', $obj->amount_one_user), ['min' => 0, 'step' => '1']) !!}
                        </div>
                    </div>
                </div>
{{--                <div class="row">--}}
{{--                    <div class="col">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('weight', __('cms-catalog::admin.products.Weight')) !!}--}}
{{--                            {!! Form::number('weight', old('weight', $obj->weight), ['min' => 0, 'step' => '100']) !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('published_at', __('cms-catalog::admin.products.Published at')) !!}
                            {!! Form::text('published_at', old('published_at', $obj->published_at ? $obj->published_at->format('d.m.Y') : null), ['class' => 'js-datepicker', 'placeholder' => __('cms-catalog::admin.products.Published at')]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('expires_at', __('cms-catalog::admin.products.Expires at')) !!}
                            {!! Form::text('expires_at', old('expires_at', $obj->expires_at ? $obj->expires_at->format('d.m.Y') : null), ['class' => 'js-datepicker', 'placeholder' => __('cms-catalog::admin.products.Expires at')]) !!}
                        </div>
                    </div>
                </div>
                @if (!$isProvider)
                    <div class="form-group">
                        {!! Form::label('provider_id', __('cms-providers::admin.provider.Provider')) !!}
                        <div class="input-group">
                            {!! Form::select('provider_id', $providers, old('provider_id', $selectedProvider), ['class' => 'js-select2']) !!}
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('moderator_id', __('cms-core::admin.moderator.moderator')) !!}
                        <div class="input-group">
                            {!! Form::select('moderator_id', $moderators, old('moderator_id', $selectedModerator), ['class' => 'js-select2']) !!}
                        </div>
                    </div>
                @endif
                <div class="form-group">
                    {!! Form::label('labels[]', __('cms-catalog::admin.labels.names')) !!}
                    <div class="input-group">
                        {!! Form::select('labels[]', $labels, old('labels', $selectedLabels), ['multiple' => 'multiple', 'class' => 'js-select2']) !!}
                    </div>
                </div>
                <div class="form-group mb-4">
                    {!! Form::label('weight', __('cms-catalog::admin.products.Weight')) !!}
                    {!! Form::number('weight', old('weight', $obj->weight), ['min' => 0, 'step' => '1']) !!}
                </div>
                <hr class="mb-0">
                <fieldset>
                    <legend class="col-form-label">
                        @lang('cms-catalog::admin.Dimensions (сm)')
                    </legend>
                    <div class="form-group row mb-0">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('dimensions[0]', __('cms-catalog::admin.products.dimensions.Width')) !!}
                                {!! Form::number(
                                    'dimensions[0]',
                                    old('dimensions[0]', $obj->dimensions[0] ?? null),
                                    ['min' => 1, 'step' => '1']
                                ) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('dimensions[1]', __('cms-catalog::admin.products.dimensions.Length')) !!}
                                {!! Form::number(
                                    'dimensions[1]',
                                    old('dimensions[1]', $obj->dimensions[1] ?? null),
                                    ['min' => 1, 'step' => '1']
                                ) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('dimensions[2]', __('cms-catalog::admin.products.dimensions.Height')) !!}
                                {!! Form::number(
                                    'dimensions[2]',
                                    old('dimensions[2]', $obj->dimensions[2] ?? null),
                                    ['min' => 1, 'step' => '1']
                                ) !!}
                            </div>
                        </div>
                    </div>
                </fieldset>
                <hr class="mt-0">

                {{--                <div class="form-group">--}}
{{--                    {!! Form::label('category_id', __('cms-catalog::admin.products.Category')) !!}--}}
{{--                    <select name="category_id" id="category_id" class="form-control js-select2">--}}
{{--                        <option value="">@lang('cms-core::admin.layout.Not set')</option>--}}
{{--                        @foreach($providers as $id => $provider)--}}
{{--                            <option value="{{ $id }}" {{ $id == old('provider_id', $obj->provider_id ?: request()->get('provider_id')) ? 'selected': null }}--}}
{{--                                >{!! $provider['name'] !!}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                <div class="row js-percentage-wrapper">--}}
{{--                    <div class="col-md-6">--}}
{{--                        <div class="form-group">--}}
{{--                            {!! Form::label('discount_percentage', __('cms-catalog::admin.products.Discount percentage')) !!}--}}
{{--                            <div class="input-group">--}}
{{--                                {!! Form::text('discount_percentage', null, ['class' => 'js-product-percentage']) !!}--}}
{{--                                <div class="input-group-append">--}}
{{--                                    <button class="btn btn-outline-secondary js-product-discount" type="button"--}}
{{--                                            title="@lang('cms-catalog::admin.products.Calculate discount price')"><i--}}
{{--                                                class="fa fa-calculator"></i></button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}

{{--                @foreach(event('catalog:product:form', ['product' => $obj]) as $eventData)--}}
{{--                    {!! $eventData !!}--}}
{{--                @endforeach--}}

{{--                <div class="form-group">--}}
{{--                    {!! Form::multipleInputs('videos[]', old('videos', $obj->videos), __('cms-catalog::admin.products.Videos')) !!}--}}
{{--                </div>--}}
            </div>
        </div>
    </div>
    <div class="col-md-12">
        @if($obj->exists)
            {!! Form::imageMultiUploader(\WezomCms\Catalog\Models\ProductImage::class, $obj->id) !!}
        @endif
    </div>
</div>

