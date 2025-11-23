<p>
    <a href="#" onclick="event.preventDefault(); document.getElementById('verification-form-logout').submit();">
        @lang('cms-users::site.Выйти')
    </a>
</p>
<form id="verification-form-logout" action="{{ route('cabinet.logout') }}" method="POST" hidden>@csrf</form>
