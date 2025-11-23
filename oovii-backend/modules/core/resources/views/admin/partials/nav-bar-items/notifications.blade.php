<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle text-muted position-relative" href="#" data-toggle="dropdown" aria-haspopup="true"
       aria-expanded="false"><i class="fa fa-bell"></i>
        @if($hasUnread)
            <div class="notify"><span class="heartbit"></span> <span class="point"></span></div>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-right mailbox animated zoomIn">
        <ul>
            <li>
                <div class="drop-title">@lang('cms-core::admin.notifications.Notifications')</div>
            </li>
            <li>
                @if(count($notifications))
                    <div class="message-center js-slim-scroll">
                    @foreach($notifications as $notification)
                        @php
                            $permission = array_get($notification->data, 'permission', str_replace('admin.', '', array_get($notification->data, 'route_name', '')));
                        @endphp

                        <!-- Message -->
                            <a href="{{ Gate::allows($permission) ? route(array_get($notification->data, 'route_name'), array_get($notification->data, 'route_params')) : '#' }}"
                               title="{{ array_get($notification->data, 'description')  }}"
                               class="js-mark-notification-as-read" data-notification-id="{{ $notification->id }}">
                                <div class="btn btn-{{ array_get($notification->data, 'color', 'success') }} rounded-circle m-r-10 notification-icon">
                                    <i class="fa {{ array_get($notification->data, 'icon', 'fa-link') }}"></i></div>
                                <div class="mail-contnet">
                                    <h6>{{ array_get($notification->data, 'heading') }}</h6>
                                    <span class="mail-desc">{{ array_get($notification->data, 'description') }}</span>
                                    <span class="time">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center">@lang('cms-core::admin.notifications.No notifications')</div>
                @endif
            </li>
            @if($hasUnread)
                <li>
                    <a class="nav-link text-center js-mark-notifications-as-read" href="#"><i class="fa fa-check"></i>
                        <strong>@lang('cms-core::admin.notifications.Mark as read')</strong></a>
                </li>
            @endif
        </ul>
    </div>
</li>
