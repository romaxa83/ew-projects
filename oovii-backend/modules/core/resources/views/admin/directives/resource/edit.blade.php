@php
    use Illuminate\Database\Eloquent\Model;
    use WezomCms\Core\Contracts\PermissionsContainerInterface;

    /**
     * @var $obj Model
     * @var $text string|bool|null
     * @var $ability string|null
     * @var $route string|null
     * @var $target string|null
     */

    $text = $text ?? null;
    $target = $target ?? null;

    if (empty($ability)) {
        $ability = app(PermissionsContainerInterface::class)->getModelAbility($obj, 'edit');
    }
    if (empty($route)) {
        $route = 'admin.' . $ability;
    }
@endphp
@if($text === false)
    {{-- render icon --}}
    @if(Gate::allows($ability, $obj))
        <a href="{{ route($route, $obj) }}"
           class="btn btn-primary"
           title="@lang('cms-core::admin.layout.Edit')"
           @if($target) target="{{ $target }}" @endif
        ><i class="fa fa-pencil"></i></a>
    @endif
@else
    @php
        if ($text === null) {
            $text = $obj->name;
        }
    @endphp
    @can($ability, $obj)
        <a href="{{ route($route, $obj) }}"
           title="{{ $text }}"
           @if($target) target="{{ $target }}" @endif
           @if($text) class="text-primary" @endif
        >{{ $text ?? __('cms-core::admin.layout.Not set') }}</a>
    @else
        <span title="{{ $text }}"
              @if($text) class="text-primary" @endif
        >{{ $text ?? __('cms-core::admin.layout.Not set') }}</span>
    @endcan
@endif
