{!! Form::open(['url' => route('admin.products.change-category'), 'class' => 'js-change-category-form js-force-valid']) !!}
<div class="modal-header">
    <h5 class="modal-title" id="change-category-label">@lang('cms-catalog::admin.products.Select new category')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<div class="modal-body">
    <div class="form-group">
        {!! Form::label('change_category_id', __('cms-catalog::admin.products.Category')) !!}
        <select name="category_id" id="change_category_id" class="form-control js-select2" required>
            <option value="">@lang('cms-core::admin.layout.Not set')</option>
            @foreach($categoriesTree as $key => $category)
                <option value="{{ $key }}" {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('cms-core::admin.layout.Cancel')</button>
    <button type="submit" class="btn btn-primary">@lang('cms-core::admin.layout.Save')</button>
</div>
@foreach(Request::get('IDS', []) as $id)
    <input type="hidden" name="IDS[]" value="{{ $id }}">
@endforeach
{!! Form::close() !!}
<script>
    window.inits.select2();
    window.inits.simpleAjaxFormSubmit('.js-change-category-form');
    window.inits.forceValid();
</script>
