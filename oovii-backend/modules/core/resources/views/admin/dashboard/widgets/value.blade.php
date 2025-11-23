@if($url)
    <a href="{{ $url }}" title="{{ $description }}" class="dashboard-item p-15">
        <span class="dashboard-item__info">
            <span class="dashboard-item__title">{{ $description }}</span>
            <span class="dashboard-item__count">{{ $value }}</span>
        </span>
        <span class="dashboard-item__icon">
            <i class="fa {{ $icon }} f-s-36 {{ $iconColorClass }}"
               @if($iconColor) style="color:{{ $iconColor }}" @endif></i>
        </span>
    </a>
@else
    <div class="dashboard-item p-15">
        <div class="dashboard-item__info">
            <div class="dashboard-item__title">{{ $description }}</div>
            <div class="dashboard-item__count">{{ $value }}</div>
        </div>
        <div class="dashboard-item__icon">
            <i class="fa {{ $icon }} f-s-36 {{ $iconColorClass }}"
               @if($iconColor) style="color:{{ $iconColor }}" @endif></i>
        </div>
    </div>
@endif
