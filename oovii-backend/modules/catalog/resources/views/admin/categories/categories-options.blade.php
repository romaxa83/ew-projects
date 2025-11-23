<option value="">@lang('cms-catalog::admin.products.Category')</option>
@foreach($tree as $key => $category)
    <option value="{{ $key }}" {{ $key == request()->get($name ?? 'category_id') ? 'selected': null }}
            {{ $category['disabled'] ?? false ? 'disabled' : null }}>{!! $category['name'] !!}</option>
@endforeach
