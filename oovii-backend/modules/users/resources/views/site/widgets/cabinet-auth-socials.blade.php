@php
/**
 * @var $socials array
 */
@endphp
<div>
    <div>
        @lang('cms-users::site.Связывайте учетную запись с учетными записями соцсетей и входите на сайт, как пользователь Facebook или Google')
    </div>
    <ul>
        @foreach($socials as $key => $name)
            <li>
                <a href="{{ route('auth.socialite', $key) }}"
                   rel="nofollow" title="@lang('cms-users::site.Связать с :social', ['social' => $name])">
                    @svg('common', $key, 20)
                    <span>{{ $name }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
