@php
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    /**
     * @var $obj Model|SoftDeletes
     * @var $ability string|null
     * @var $route string|null
     * @var $formButton bool|null
     */
    $formButton = $formButton ?? false;

    $isSoftDeletes = method_exists($obj, 'trashed');
    $trashed = $isSoftDeletes && $obj->trashed();

     if (empty($ability)) {
        $ability = (property_exists($obj, 'abilityPrefix') ? $obj->abilityPrefix : str_replace('_', '-', $obj->getTable())) . '.restore';
     }
     if (empty($route)) {
        $route = 'admin.' . $ability;
     }
@endphp
@if($trashed && Gate::allows($ability, $obj))
    <form action="{{ route($route, $obj) }}" method="POST" class="list_control_form_btn btn">
        {!! method_field('PATCH') !!}
        {!! csrf_field() !!}
        <button class="btn btn-warning {{ $formButton ? 'btn-sm' : '' }}"
                title="@lang('cms-core::admin.layout.Restore')"><i
                    class="fa fa-undo"></i>@if($formButton) <span class="hidden-sm-down">@lang('cms-core::admin.layout.Restore')</span>@endif</button>
    </form>
@endif
