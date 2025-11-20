<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle text-muted" href="#" data-toggle="dropdown" aria-haspopup="true"
       aria-expanded="false"><i class="fa fa-flag"></i></a>
    <div class="dropdown-menu dropdown-menu-right animated slideInUp">
        <ul class="dropdown-user">
            @foreach($locales as $locale => $lang)
                <li>
                    <a href="{{ route('admin.change-locale', $locale) }}"
                       class="{{ $locale === app()->getLocale() ? 'text-success' : '' }}">{{ $lang['name'] }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</li>
