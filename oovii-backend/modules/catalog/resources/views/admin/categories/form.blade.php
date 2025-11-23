<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @tabs( [
                __('cms-catalog::admin.categories.Main data') => $viewPath.'.tabs.main-data',
{{--                __('cms-catalog::admin.categories.SEO') => $viewPath.'.tabs.seo'--}}
                ])
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published') !!}
                </div>
                <div class="form-group">
                    {!! Form::label('parent_id', __('cms-catalog::admin.categories.Parent')) !!}
                    {!! Form::select('parent_id', $tree, old('parent_id', $obj->parent_id ?: request()->get('parent_id')), ['class' => 'js-select2']) !!}
                </div>
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('CATEGORIES[]', __('cms-catalog::admin.category.Список категорий с этим товаром покупают')) !!}--}}
{{--                    <select multiple="multiple" name="CATEGORIES[]" id="CATEGORIES[]" class="form-control js-select2">--}}
{{--                        <option value="">@lang('cms-core::admin.layout.Not set')</option>--}}
{{--                        @foreach($categoriesTree as $key => $category)--}}
{{--                            <option value="{{ $key }}" {{ in_array($key, $selectedCategories) ? 'selected': null }}--}}
{{--                                    {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('show_on_main', __('cms-catalog::admin.categories.Show on main')) !!}--}}
{{--                    {!! Form::status('show_on_main', null, false) !!}--}}
{{--                </div>--}}
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('image', __('cms-catalog::admin.categories.Image')) !!}--}}
{{--                    {!! Form::imageUploader('image', $obj) !!}--}}
{{--                </div>--}}
{{--                <div class="form-group">--}}
{{--                    {!! Form::label('icon', __('cms-catalog::admin.categories.Icon')) !!}--}}
{{--                    {!! Form::imageUploader('icon', $obj) !!}--}}
{{--                </div>--}}
            </div>
        </div>
    </div>
</div>
