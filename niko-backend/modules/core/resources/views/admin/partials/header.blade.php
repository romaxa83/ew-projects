<div class="header">
    <nav class="navbar top-navbar navbar-expand-md navbar-light">
        <!-- Logo -->
        <div class="navbar-header">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">

{{--                @if($wideLogo)--}}
                    <img src="{{ url('images/niko.png') }}" alt="{{ config('app.name') }}" class="logo-wide">
{{--                @else--}}
{{--                    <div class="logo-wide">{{ config('app.name') }}</div>--}}
{{--                @endif--}}
{{--                @if($smallLogo)--}}
                    <img src="{{ url('images/niko.png') }}" alt="{{ config('app.name') }}" class="logo-small">
{{--                @else--}}
{{--                    <div class="logo-small">{{ substr(config('app.name'), 0, 2) }}</div>--}}
{{--                @endif--}}
            </a>
        </div>
        <!-- End Logo -->
        <div class="navbar-collapse">
            <!-- toggle and nav items -->
            <ul class="navbar-nav mr-auto mt-md-0">
                <!-- This is  -->
                <li class="nav-item"><a class="nav-link nav-toggler hidden-md-up text-muted"
                                        href="javascript:void(0)"><i class="fa fa-bars"></i></a></li>
                <li class="nav-item m-l-10"><a class="nav-link sidebartoggler hidden-sm-down text-muted"
                                               href="javascript:void(0)"><i class="fa fa-bars"></i></a></li>
            </ul>
            <!-- User profile and search -->
            <ul class="navbar-nav my-lg-0">
            @foreach($navBarItems as $navBarItem)
                {!! $navBarItem->toHtml() !!}
            @endforeach
            <!-- Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-muted" href="#" data-toggle="dropdown" aria-haspopup="true"
                       aria-expanded="false"><i class="fa fa-user-circle-o profile-icon"></i></a>
                    <div class="dropdown-menu dropdown-menu-right animated flipInX">
                        <ul class="dropdown-user">
                            <li><a href="{{ route('admin.edit-profile') }}"><i
                                            class="ti-user"></i> @lang('cms-core::admin.profile.Profile')</a></li>
                            <li><a href="#"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                ><i class="fa fa-power-off"></i> @lang('cms-core::admin.profile.Logout')</a></li>
                        </ul>
                        <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
                              style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</div>
