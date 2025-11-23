@php
    /**
     * @var $socialAccounts \Illuminate\Database\Eloquent\Collection|\WezomCms\Users\Models\SocialAccount[]
     * @var $supportedSocials array
     */
@endphp
<div>@lang('cms-users::site.Войти как пользователь')</div>
@if($socialAccounts->isNotEmpty())
    <ul data-unlinks>
        @foreach($socialAccounts as $social)
            <li>
                <a href="{{ route('auth.socialite.disconnect', $social->id) }}"
                   class="{{ $social->provider }}"
                   title="@lang('cms-users::site.Аккаунт'): {{ $social->full_name }}"
                   data-unlink="@lang('cms-users::site.Вы точно хотите удалить связь с аккаунтом') {{ $social->full_name }}?">
                    @svg('common', $social->provider, 18)
                    <span>{{ $social->full_name }}</span>
                    <span>@lang('cms-users::site.Отменить связь')</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif

@if(count($supportedSocials))
    <ul>
        @foreach($supportedSocials as $social)
            <li>
                <a href="{{ route('auth.socialite', $social) }}"
                   class="{{$social}}" rel="nofollow" title="@lang('cms-users::site.Связать с'): {{ $social }}">
                    @svg('common', $social, 18)
                    <span>{{ $social }}</span>
                    <span>@lang('cms-users::site.Связать с аккаунтом')</span>
                </a>
            </li>
        @endforeach
    </ul>
@endif
