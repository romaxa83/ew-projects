<aside class="page-sidebar">
    <div class="page-logo">
        <a href="{{ route('home') }}"
           class="page-logo-link press-scale-down d-flex align-items-center position-relative" data-toggle="modal"
           data-target="#modal-shortcut">
            <img src="{{ asset('/smartadmin/img/logo.png') }}" alt="{{ config('app.name') }}"
                 aria-roledescription="logo">
            <span class="page-logo-text mr-1">{{ config('app.name') }}</span>
            <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
            <i class="fal fa-angle-down d-inline-block ml-1 fs-lg color-primary-300"></i>
        </a>
    </div>
    <!-- BEGIN PRIMARY NAVIGATION -->
    <nav id="js-primary-nav" class="primary-nav" role="navigation">
        <div class="nav-filter">
            <div class="position-relative">
                <input type="text" id="nav_filter_input" placeholder="Filter menu" class="form-control" tabindex="0">
                <a href="#" onclick="return false;" class="btn-primary btn-search-close js-waves-off"
                   data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar">
                    <i class="fal fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="info-card">
            <img src="https://www.gravatar.com/avatar/{{ md5(Auth::user()->email) }}?d=identicon"
                 class="profile-image rounded-circle" alt="{{ Auth::user()->name }}">
            <div class="info-card-text">
                <a href="{{ route('home') }}" class="d-flex align-items-center text-white">
                    <span class="text-truncate text-truncate-sm d-inline-block">
                        {{ Auth::user()->name }}
                    </span>
                </a>
                <span class="d-inline-block text-truncate text-truncate-sm">Toronto, Canada</span>
            </div>
            <img src="{{ asset('/smartadmin/img/card-backgrounds/cover-2-lg.png') }}" class="cover" alt="cover">
            <a href="#" onclick="return false;" class="pull-trigger-btn" data-action="toggle"
               data-class="list-filter-active" data-target=".page-sidebar" data-focus="nav_filter_input">
                <i class="fal fa-angle-down"></i>
            </a>
        </div>
        <ul id="js-nav-menu" class="nav-menu">
            @if (!Auth::user()->isPartner())
                <li{!! Route::currentRouteName() === 'home' ? ' class="active open"' : '' !!}>
                    <a href="{{ route('home') }}" title="Dashboard" data-filter-tags="dashboard main">
                        <i class="fal fa-window"></i>
                        <span class="nav-link-text">Dashboard</span>
                    </a>
                </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('clients.'))
            <li{!! (Str::startsWith(Route::currentRouteName(), 'clients.')) ? ' class="active open"' : '' !!}>
                <a href="{{ route('clients.records') }}" title="Clients" data-filter-tags="Client">
                    <i class="fal fa-users"></i>
                    <span class="nav-link-text">Clients</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('orders.'))
                @if (!Auth::user()->isPartner())
                        <li{!! (Str::startsWith(Route::currentRouteName(), 'orders.') && Route::currentRouteName() !== 'orders.pipeline.list') ? ' class="active open"' : '' !!}>
                            <a href="{{ route('orders.records') }}" title="Orders" data-filter-tags="orders moving">
                                <i class="fal fa-truck-moving"></i>
                                <span class="nav-link-text">Orders</span>
                            </a>
                        </li>
                @endif
            @endif
            @if(Auth::user()->isRoutePatternAllowed('orders.pipeline.'))
            <li{!! Route::currentRouteName() === 'orders.pipeline.list' ? ' class="active open"' : '' !!}>
                <a href="{{ route('orders.pipeline.list') }}" title="Orders Pipeline" data-filter-tags="orders pipeline">
                    <i class="fal fa-funnel-dollar"></i>
                    <span class="nav-link-text">Orders Pipeline</span>
                </a>
            </li>
            @endif
{{--            @if(Auth::user()->isRoutePatternAllowed('communications.list'))--}}
{{--            <li{!! Route::currentRouteName() === 'communications.list' ? ' class="active open"' : '' !!}>--}}
{{--                <a href="{{ route('communications.list') }}" title="Inbound communications" data-filter-tags="Inbound communications">--}}
{{--                    <i class="fal fa-comments"></i>--}}
{{--                    <span class="nav-link-text">Communications OLD</span>--}}
{{--                </a>--}}
{{--            </li>--}}
{{--            @endif--}}
            @if(Auth::user()->isRoutePatternAllowed('communications.list'))
                <li{!! Route::currentRouteName() === 'communications.v2.list' ? ' class="active open"' : '' !!}>
                    <a
                        href="{{ route('communications.v2.list') }}"
                        title="Inbound communications"
                        data-filter-tags="Inbound communications"
                    >
                        <i class="fal fa-comments"></i>
                        <span class="nav-link-text">Communications</span>
                    </a>
                </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('tasks.tasks-calendar'))
            <li{!! Route::currentRouteName() === 'tasks.tasks-calendar' ? ' class="active open"' : '' !!}>
                <a href="{{ route('tasks.tasks-calendar') }}" title="Schedule" data-filter-tags="tasks calendar">
                    <i class="fal fa-tasks"></i>
                    <span class="nav-link-text">Tasks Calendar</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('calendar.schedule'))
            <li{!! Route::currentRouteName() === 'calendar.schedule' ? ' class="active open"' : '' !!}>
                <a href="{{ route('calendar.schedule') }}" title="Schedule" data-filter-tags="schedule calendar">
                    <i class="fal fa-calendar-edit"></i>
                    <span class="nav-link-text">Calendar</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('dispatch.schedule'))
            <li{!! Route::currentRouteName() === 'dispatch.schedule' ? ' class="active open"' : '' !!}>
                <a href="{{ route('dispatch.schedule') }}" title="Schedule" data-filter-tags="dispatch moving">
                    <i class="fal fa-person-dolly-empty"></i>
                    <span class="nav-link-text">Dispatch</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('settings.'))
                <li{!! Str::startsWith(Route::currentRouteName(), 'settings.') ? ' class="active open"' : '' !!}>
                    <a href="#" title="Settings" data-filter-tags="config settings">
                        <i class="fal fa-cog"></i>
                        <span class="nav-link-text" data-i18n="nav.ui_components">Settings</span>
                    </a>
                    <ul>
                        <li {!! Str::startsWith(Route::currentRouteName(), 'settings.divisions') ? ' class="active open"' : '' !!} data-filter-tags="Divisions">
                            <a href="javascript:void(0);" title="Divisions" data-filter-tags="rate">
                                <span class="nav-link-text" data-i18n="nav.font_icons_fontawesome">Divisions</span>
                            </a>
                            <ul>
                                <li {!! Route::currentRouteName() === 'settings.divisions.divisions.index' ? ' class="active"' : '' !!} title="Divisions"
                                    data-filter-tags="Divisions">
                                    <a href="{{ route('settings.divisions.divisions.index') }}"><span
                                            class="nav-link-text">Divisions</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.divisions.divisions-footer-texts.index' ? ' class="active"' : '' !!} title="Customer page texts"
                                    data-filter-tags="Customer page texts">
                                    <a href="{{ route('settings.divisions.divisions-footer-texts.index') }}"><span
                                            class="nav-link-text">Customer page texts</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.materials.home' ? ' class="active"' : '' !!} title="Items"
                                    data-filter-tags="materials goods additional">
                                    <a href="{{ route('settings.materials.home') }}"><span class="nav-link-text">Materials</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.email-templates.records' ? ' class="active"' : '' !!} title="Email Templates"
                                    data-filter-tags="email templates">
                                    <a href="{{ route('settings.email-templates.records') }}"><span class="nav-link-text">Email Templates</span></a>
                                </li>
                            </ul>
                        </li>
                        <li{!! Str::startsWith(Route::currentRouteName(), 'settings.orders.') ? ' class="active open"' : '' !!}>
                            <a href="javascript:void(0);" title="Orders" data-filter-tags="orders">
                                <span class="nav-link-text" data-i18n="nav.font_icons_fontawesome">Orders</span>
                            </a>
                            <ul>
                                <li {!! Route::currentRouteName() === 'settings.orders.statuses' ? ' class="active"' : '' !!} title="Statuses"
                                    data-filter-tags="statuses">
                                    <a href="{{ route('settings.orders.statuses') }}"><span class="nav-link-text">Statuses</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.orders.statuses.groups' ? ' class="active"' : '' !!} title="Statuses"
                                    data-filter-tags="groups">
                                    <a href="{{ route('settings.orders.statuses.groups') }}"><span
                                            class="nav-link-text">Statuses Groups</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.orders.statuses.routes' ? ' class="active"' : '' !!} title="Statuses"
                                    data-filter-tags="routes">
                                    <a href="{{ route('settings.orders.statuses.routes') }}"><span
                                            class="nav-link-text">Statuses Routes</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.orders.sources' ? ' class="active"' : '' !!} title="Sources"
                                    data-filter-tags="sources">
                                    <a href="{{ route('settings.orders.sources') }}"><span
                                            class="nav-link-text">Sources</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.orders.tags' ? ' class="active"' : '' !!} title="Order Tags"
                                    data-filter-tags="Order Tags">
                                    <a href="{{ route('settings.orders.tags') }}"><span class="nav-link-text">Tags</span></a>
                                </li>
                            </ul>
                        </li>
                        <li {!! Route::currentRouteName() === 'settings.items' ? ' class="active"' : '' !!} title="Items"
                            data-filter-tags="items">
                            <a href="{{ route('settings.items') }}"><span class="nav-link-text">Items</span></a>
                        </li>
                        <li{!! Str::startsWith(Route::currentRouteName(), 'settings.users.') ? ' class="active open"' : '' !!}>
                            <a href="javascript:void(0);" title="Orders" data-filter-tags="rate">
                                <span class="nav-link-text" data-i18n="nav.font_icons_fontawesome">Users</span>
                            </a>
                            <ul>
                                {{--                                <li {!! Route::currentRouteName() === 'settings.users.records' ? ' class="active"' : '' !!} title="Users"--}}
                                {{--                                    data-filter-tags="users accounts">--}}
                                {{--                                    <a href="{{ route('settings.users.records') }}"><span--}}
                                {{--                                            class="nav-link-text">Users (NOT FOR CREATE USERS)</span></a>--}}
                                {{--                                </li>--}}
                                <li {!! Route::currentRouteName() === 'settings.users.routes2roles' ? ' class="active"' : '' !!} title="Routes 2 Roles"
                                    data-filter-tags="routes roles">
                                    <a href="{{ route('settings.users.routes2roles') }}"><span class="nav-link-text">Routes 2 Roles</span></a>
                                </li>
                            </ul>
                        </li>
                        <li{!! Str::startsWith(Route::currentRouteName(), 'settings.rates.') ? ' class="active open"' : '' !!}>
                            <a href="javascript:void(0);" title="Orders" data-filter-tags="rate">
                                <span class="nav-link-text" data-i18n="nav.font_icons_fontawesome">Rates</span>
                            </a>
                            <ul>
                                <li {!! Route::currentRouteName() === 'settings.rates.employee' ? ' class="active"' : '' !!} title="Rates employee"
                                    data-filter-tags="Local rate per hour">
                                    <a href="{{ route('settings.rates.employee') }}"><span class="nav-link-text">Crew rates (Price per hour)</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.rates.local' ? ' class="active"' : '' !!} title="Rates"
                                    data-filter-tags="Local rate per hour">
                                    <a href="{{ route('settings.rates.local') }}"><span class="nav-link-text">Local (Price per hour)</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.rates.intrastate' ? ' class="active"' : '' !!} title="Rates"
                                    data-filter-tags="intrastate rate per hour">
                                    <a href="{{ route('settings.rates.intrastate') }}"><span class="nav-link-text">Intrastate rates</span></a>
                                </li>
                                <li {!! Route::currentRouteName() === 'settings.rates.interstate' ? ' class="active"' : '' !!} title="Rates"
                                    data-filter-tags="interstate rate per hour">
                                    <a href="{{ route('settings.rates.interstate') }}"><span class="nav-link-text">Interstate rates</span></a>
                                </li>
                            </ul>
                        </li>
                        @include('layouts.app.sidebar-item', [
                            'route' => 'tasks.settings.types',
                            'title' => 'Tasks Types'
                        ])
                        @include('layouts.app.sidebar-item', [
                            'route' => 'settings.client.tags',
                            'title' => 'Client Tags'
                        ])
                        @include('layouts.app.sidebar-item', [
                            'route' => 'settings.teams_plans',
                            'title' => 'Teams plans'
                        ])
                    </ul>
                </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('company.'))
                <li{!! Str::startsWith(Route::currentRouteName(), 'company.') ? ' class="active open"' : '' !!}>
                    <a href="#" title="Company" data-filter-tags="">
                        <i class="fal fa-building"></i>
                        <span class="nav-link-text" data-i18n="nav.ui_components">Company</span>
                    </a>
                    <ul>
                        @if(Auth::user()->isRoutePatternAllowed('company.trucks'))
                            <li {!! Str::startsWith(Route::currentRouteName(), 'company.trucks') ? ' class="active"' : '' !!} title="Trucks"
                                data-filter-tags="trucks cars bus vehicle">
                                <a href="{{ route('company.trucks.records') }}"><span class="nav-link-text">Trucks</span></a>
                            </li>
                        @endif
                        @if(Auth::user()->isRoutePatternAllowed('company.employees'))
                            <li {!! Str::startsWith(Route::currentRouteName(), 'company.employees') ? ' class="active"' : '' !!} title="Employees"
                                data-filter-tags="employee user account">
                                <a href="{{ route('company.employees.records') }}"><span class="nav-link-text">Employees</span></a>
                            </li>
                        @endif
                        @if(Auth::user()->isRoutePatternAllowed('company.peak_dates'))
                            <li {!! Route::currentRouteName() === 'company.peak_dates' ? ' class="active"' : '' !!} title="Peak"
                                data-filter-tags="peak date">
                                <a href="{{ route('company.peak_dates') }}"><span class="nav-link-text">Peak date &amp; Holidays</span></a>
                            </li>
                        @endif
                        @if(Auth::user()->isRoutePatternAllowed('partner'))
                            <li {!! Str::startsWith(Route::currentRouteName(), 'partner') ? ' class="active"' : '' !!} title="Partners"
                                data-filter-tags="trucks cars bus vehicle">
                                <a href="{{ route('partner.index') }}"><span class="nav-link-text">Partners</span></a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(Auth::user()->inRole(1) || Auth::user()->inRole(5))
            <li{!! Str::startsWith(Route::currentRouteName(), 'reports.') && Route::currentRouteName() !== 'reports.foreman.cash.report.index' ? ' class="active open"' : '' !!}>
                <a href="#" title="Reports" data-filter-tags="">
                    <i class="fal fa-th-list"></i>
                    <span class="nav-link-text" data-i18n="nav.ui_components">Reports</span>
                </a>
                <ul>
                    @if(Auth::user()->isRoutePatternAllowed('reports.sales-report'))
                        <li{!! Route::currentRouteName() === 'reports.sales-report.view' ? ' class="active open"' : '' !!}>
                            <a href="{{ route('reports.sales-report.view') }}" title="Sales report" data-filter-tags="Sales report">
                                <span class="nav-link-text">Sales report</span>
                            </a>
                        </li>
                    @endif
{{--                        @if(Auth::user()->isRoutePatternAllowed('financial.check.report.index'))--}}
                            <li{!! Route::currentRouteName() === 'reports.financial.check.report.index' ? ' class="active open"' : '' !!}>
                                <a href="{{ route('reports.financial.check.report.index') }}" title="Financial check report" data-filter-tags="Financial check report">
                                    <span class="nav-link-text">Financial check report</span>
                                </a>
                            </li>
{{--                        @endif--}}
                        <li{!! Route::currentRouteName() === 'reports.sales.funel.report.index' ? ' class="active open"' : '' !!}>
                            <a href="{{ route('reports.sales.funel.report.index') }}" title="Sales funel report" data-filter-tags="Sales funel report">
                                <span class="nav-link-text">Sales funel report</span>
                            </a>
                        </li>
                    <li{!! Route::currentRouteName() === 'reports.callLog' ? ' class="active open"' : '' !!}>
                        <a href="{{ route('reports.callLog') }}" title="Calls report" data-filter-tags="Calls report">
                            {{--                            <i class="fal fa-phone"></i>--}}
                            <span class="nav-link-text">Calls report</span>
                        </a>
                    </li>
                    <li{!! Route::currentRouteName() === 'reports.activity-audit-report' ? ' class="active open"' : '' !!}>
                        <a href="{{ route('reports.activity-audit-report') }}" title="Activity report" data-filter-tags="Activity report">
                            {{--                            <i class="fal fa-phone"></i>--}}
                            <span class="nav-link-text">Activity report</span>
                        </a>
                    </li>
                    @if(Auth::user()->isRoutePatternAllowed('reports.efficiency-report'))
                    <li{!! Route::currentRouteName() === 'reports.efficiency-report.view' ? ' class="active open"' : '' !!}>
                        <a href="{{ route('reports.efficiency-report.view') }}" title="Efficiency report" data-filter-tags="Efficiency report">
                            <span class="nav-link-text">Efficiency report</span>
                        </a>
                    </li>
                    @endif
                    <li {!! Str::startsWith(Route::currentRouteName(), 'reports.report1') ? ' class="active"' : '' !!} title="Report 1"
                        data-filter-tags="Report By Managers">
                        <a href="{{ route('reports.report1') }}"><span class="nav-link-text">By Managers</span></a>
                    </li>
                    <li {!! Str::startsWith(Route::currentRouteName(), 'reports.effective-actions') ? ' class="active"' : '' !!} title="Analysis of effective actions"
                        data-filter-tags="Report Analytics actions">
                        <a href="{{ route('reports.effective-actions') }}"><span class="nav-link-text">Analytics of effective actions</span></a>
                    </li>
                    <li {!! Str::startsWith(Route::currentRouteName(), 'reports.by-managers') ? ' class="active"' : '' !!} title="Analytics"
                        data-filter-tags="Report Analytics Managers Company">
                        <a href="{{ route('reports.by-managers') }}"><span class="nav-link-text">Analytics by Managers and Company</span></a>
                    </li>
                    <li {!! Str::startsWith(Route::currentRouteName(), 'reports.authorize') ? ' class="active"' : '' !!} title="Analytics"
                        data-filter-tags="Report Analytics Managers Company">
                        <a href="{{ route('reports.authorize') }}"><span class="nav-link-text">Authorize Transactions</span></a>
                    </li>
                </ul>
            </li>
            @endif
            @if(Auth::user()->isRoutePatternAllowed('mailbox.home'))
            <li{!! Str::startsWith(Route::currentRouteName(), 'mailbox.') ? ' class="active open"' : '' !!}>
                <a href="{{ route('mailbox.home') }}" title="MailBox" data-filter-tags="">
                    <i class="fal fa-mailbox"></i>
                    <span class="nav-link-text" data-i18n="nav.ui_components">MailBox</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->isAccountant())
                <li{!! Str::startsWith(Route::currentRouteName(), 'cash-registry.') || Route::currentRouteName() === 'reports.foreman.cash.report.index' ? ' class="active open"' : '' !!}>
                    <a href="#" title="Accounting" data-filter-tags="">
                        <i class="fal fa-money-bill"></i>
                        <span class="nav-link-text" data-i18n="nav.ui_components">Accounting</span>
                    </a>
                    <ul>
                        <li{!! Route::currentRouteName() === 'reports.foreman.cash.report.index' ? ' class="active open"' : '' !!}>
                            <a href="{{ route('reports.foreman.cash.report.index') }}"
                               title="Foreman cash report"
                               data-filter-tags="Foreman cash report">
                                <span class="nav-link-text">Foreman cash report</span>
                            </a>
                        </li>
                        <li{!! Route::currentRouteName() === 'cash-registry.foremans.index' ? ' class="active open"' : '' !!}>
                            <a href="{{ route('cash-registry.foremans.index') }}"
                               title="Cash Registry"
                               data-filter-tags="Cash registry">
                                <span class="nav-link-text">Cash Registry</span>
                            </a>
                        </li>
                        <li{!! Route::currentRouteName() === 'cash-registry.operations.index' ? ' class="active open"' : '' !!}>
                            <a href="{{ route('cash-registry.operations.index') }}"
                               title="Cash Registry History"
                               data-filter-tags="Cash registry history">
                                <span class="nav-link-text">Cash Registry History</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
        <div class="filter-message js-filter-message bg-success-600"></div>
    </nav>
    <!-- END PRIMARY NAVIGATION -->
    <!-- NAV FOOTER -->
    <div class="nav-footer shadow-top">
        <a href="#" onclick="return false;" data-action="toggle" data-class="nav-function-minify"
           class="hidden-md-down">
            <i class="ni ni-chevron-right"></i>
            <i class="ni ni-chevron-right"></i>
        </a>
        <ul class="list-table m-auto nav-footer-buttons">
            <li>
                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Chat logs">
                    <i class="fal fa-comments"></i>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Support Chat">
                    <i class="fal fa-life-ring"></i>
                </a>
            </li>
            {{--            <li>--}}
            {{--                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Make a call">--}}
            {{--                    <i class="fal fa-phone"></i>--}}
            {{--                </a>--}}
            {{--            </li>--}}
        </ul>
    </div> <!-- END NAV FOOTER -->
</aside>
