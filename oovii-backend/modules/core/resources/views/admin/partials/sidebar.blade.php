<div class="scroll-sidebar">
    <!-- Sidebar navigation-->
    <nav class="sidebar-nav">
        <div class="sidebar-search-wrap">
            <label class="sidebar-search">
                <input type="search" id="sidebar-search-input" class="sidebar-search-input form-control" placeholder="@lang('cms-core::admin.menu.Search')...">
                <span id="sidebar-clear-search-input" class="sidebar-clear-search-input" hidden><i class="fa fa-close"></i></span>
            </label>
        </div>
        <ul id="sidebarnav">
            <li hidden>
                <a href="javascript:void(0);">
                    <i class="fa fa-frown-o"></i>
                    <span class="hide-menu d-inline-block align-middle">@lang('cms-core::admin.menu.Nothing found')</span>
                </a>
            </li>
            @foreach($sidebarMenu->roots() as $item)
                @include('cms-core::admin.partials.sidebar-menu-item', ['item' => $item, 'parent' => $item, 'root' => true])
            @endforeach
        </ul>
    </nav>
    <!-- End Sidebar navigation -->
</div>
