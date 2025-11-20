@php
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    /**
     * @var $obj Model|SoftDeletes
     * @var $title string|null
     * @var $text string|null
     * @var $yesText string|null
     * @var $noText string|null
     * @var $ability string|null
     * @var $route string|null
     */
    $isSoftDeletes = method_exists($obj, 'trashed');
    $trashed = $isSoftDeletes && $obj->trashed();

     if (empty($ability)) {
        $ability = (property_exists($obj, 'abilityPrefix') ? $obj->abilityPrefix : str_replace('_', '-', $obj->getTable())) . '.' . ($trashed ? 'force-delete' : 'delete');
     }
     if (empty($route)) {
        $route = str_replace('delete', 'destroy', 'admin.' . $ability);
     }
@endphp
@if(Gate::allows($ability, $obj))
    <form action="{{ route($route, $obj) }}" method="POST" class="list_control_form_btn btn">
        {!! method_field('DELETE') !!}
        {!! csrf_field() !!}
        <button class="btn btn-danger"
                onclick="return confirmDelete(
                    this,
                    '{{ $title ?? __('cms-core::admin.layout.Are you sure?') }}',
                    '{{ $text ?? ($isSoftDeletes && !$trashed ? __('cms-core::admin.layout.You can recover in the deleted section') : __('cms-core::admin.layout.This action is irreversible!')) }}',
                    '{{ $yesText ?? __('cms-core::admin.layout.Yes, delete it') }}',
                    '{{ $noText ?? __('cms-core::admin.layout.Cancel') }}')"
                title="@lang('cms-core::admin.layout.Delete')"><i class="fa fa-trash"></i></button>
    </form>
@endif
