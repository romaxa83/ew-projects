@if($record->works_count === 1)
    @php ($work = $record->works->first())
    <div class="mb-2">
        <small class="text-muted d-block mb-1">
            @if($work->start_date) {{Carbon\Carbon::parse($work->start_date)->format("M d, Y")}} @endif
            @if($work->start_time) {{Carbon\Carbon::createFromFormat("H:i:s", $work->start_time)->format('H:i')}} @endif
        </small>
        @foreach($work->workTypes as $workType)
            <button type="button"
                    class="btn btn-xs btn-primary waves-effect waves-themed mb-1">{{$workType->title}}</button>
        @endforeach
    </div>
@elseif($record->works_count > 1)
    @php ($work = $record->works->first())
    <div class="mb-2">
        <small class="text-muted d-block mb-1">
            Start from:
            @if($work->start_date) {{Carbon\Carbon::parse($work->start_date)->format("M d, Y")}} @endif
            @if($work->start_time) {{Carbon\Carbon::createFromFormat("H:i:s", $work->start_time)->format('H:i')}} @endif
        </small>
        @foreach($record->all_works as $workType)
            <button type="button" class="btn btn-xs btn-primary waves-effect waves-themed mb-1">{{ $workType }}</button>
        @endforeach
    </div>
@endif
