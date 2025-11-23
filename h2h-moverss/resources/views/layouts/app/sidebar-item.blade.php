<li
    title="{{ $title }}"
    data-filter-tags="{{ $tags ?? $title }}"
    class="{{ Route::currentRouteName() === $route ? 'active' : ''}}"
>
    <a href="{{ route($route) }}">
        <span class="nav-link-text">
            {{ $title }}
        </span>
    </a>
</li>
