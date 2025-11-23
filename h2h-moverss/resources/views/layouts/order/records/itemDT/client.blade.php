<div class="fs-sm">{{ $record->client ? $record->client->ClientFullName() : 'None' }}</div>

@if(!empty($record->client->emails) && $record->client->emails->count())
    <div>
        <a href="mailto:{{$record->client->emails->first()->value}}">{{$record->client->emails->first()->value}}</a>
    </div>
@endif

@if(!empty($record->client->phones) && $record->client->phones->count())
    <div class="text-dark fs-nano">
        {{ $record->client->phones->first()->value }}
    </div>

    <div class="d-flex">
        <div class="flex-fill text-left">
            @if($communicationsCount['callsSucceed'])
                <a href="javascript:void(0);"
                   data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Successful calls"
                   class="btn btn-sm btn-secondary btn-icon rounded-circle position-relative js-waves-off">
                    <i class="fas fa-phone"></i>
                    <span
                        class="badge border border-light rounded-pill bg-success-500 position-absolute pos-phone">{{$communicationsCount['callsSucceed']}}</span>
                </a>
            @endif
        </div>
        <div class="flex-fill text-center">
            @if($communicationsCount['callsFailed'])
                <a href="javascript:void(0);"
                   data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Unsuccessful calls"
                   class="btn btn-sm btn-secondary btn-icon rounded-circle position-relative js-waves-off">
                    <i class="fas fa-phone"></i>
                    <span
                        class="badge border border-light rounded-pill bg-danger-500 position-absolute pos-phone">{{$communicationsCount['callsFailed']}}</span>
                </a>
            @endif
        </div>
        <div class="flex-fill text-right">
            @if($communicationsCount['emails'])
            <a href="javascript:void(0);"
               class="btn btn-sm btn-secondary btn-icon rounded-circle position-relative js-waves-off orders-sent-emails">
                <i class="fas fa-envelope"></i>
                <span class="badge border border-light rounded-pill bg-warning-700 position-absolute pos-phone">{{$communicationsCount['emails']}}</span>
            </a>
            @endif
        </div>
    </div>
@endif

@if($record->client && $record->client->tags)
    <div class="d-flex flex-wrap">
    @foreach($record->client->tags as $v)
        @php($color = $v->color ?? '#6c757d')

        <button type="button" class="btn btn-xs mb-1 btn-secondary waves-effect waves-themed mr-1"
                style="{!!  'background-color: '.$color.';border-color: '.$color !!}">
            <i class="fas mr-1 {!! $v->icon ? 'fa-'.$v->icon:'fa-tag' !!}"></i>
            {{ $v->title }}
        </button>
    @endforeach
    </div>
@endif
