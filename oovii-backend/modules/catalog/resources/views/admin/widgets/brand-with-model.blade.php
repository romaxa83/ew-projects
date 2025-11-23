@php
/**
 * @var $brandsEnabled bool
 * @var $modelsEnabled bool
 * @var $brand array
 * @var $model array
 * @var $brand Brand|null
 * @var $model Model|null
 */
 use WezomCms\Catalog\Models\Brand;
 use WezomCms\Catalog\Models\Model;
@endphp
<div class="row">
    @if($brandsEnabled)
        <div class="col-md-{{ $modelsEnabled ? 6 : 12 }}">
            <div class="form-group">
                {!! Form::label('brand_id', __('cms-catalog::admin.products.Brand')) !!}
                {!! Form::select('brand_id', $brands, null, ['class' => 'js-ajax-select2 js-brand-selector',
                 'data-url' => route('admin.brands.search'), 'placeholder' => __('cms-core::admin.layout.Not set')]) !!}
            </div>
        </div>
    @endif
    @if($modelsEnabled)
        <div class="col-md-{{ $brandsEnabled ? 6 : 12 }}">
            <div class="form-group">
                {!! Form::label('model_id', __('cms-catalog::admin.products.Model')) !!}
                {!! Form::select('model_id', $models, null, ['class' => 'js-ajax-select2 js-model-selector',
                 'data-url' => route('admin.models.search', $brandsEnabled && $brand ? ['brand_id' => $brand->id] : []), 'placeholder' => __('cms-core::admin.layout.Not set')]) !!}
            </div>
        </div>
    @endif
</div>
