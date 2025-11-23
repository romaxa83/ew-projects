@php
    /**
     * @var $menu \Lavary\Menu\Builder
     */
@endphp
<p>@lang('cms-users::site.Мой аккаунт')</p>
@foreach($menu->roots() as $item)
    <div>
        @if($item->isActive)
            <span class="is-active">{{ $item->title }}</span>
        @elseif($item->url())
            <a href="{{ $item->url() }}"
               class="{{ $item->class }}" {!! $item->el_attributes !!}>{{ $item->title }}</a>
        @endif
    </div>
@endforeach
<div>
    <a href="#" onclick="event.preventDefault(); document.forms['logout-form'].submit();">
        @lang('cms-users::site.cabinet.Logout')
    </a>
</div>
<form id="logout-form" action="{{ route('cabinet.logout') }}" method="POST"
      style="display: none;">
    @csrf
</form>
