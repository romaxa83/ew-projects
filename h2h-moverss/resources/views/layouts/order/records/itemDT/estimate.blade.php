<div class="d-flex">
    <div class="flex-fill">
        <div>
            <small class="text-muted">Total:</small>
        </div>
        <div>
            {{ isset($record->calculated['total']) ? $record->calculated['total']['value'] : 'n/a' }}
        </div>
    </div>
    @if(isset($record->calculated['paid']))
        @if(isset($record->calculated['paid']))
            <div class="flex-fill">
                <div>
                    <small class="text-muted text-success">Actual payed:</small>
                </div>
                <div>
                    {{ $record->calculated['paid']['value'] }}
                </div>
            </div>
        @endif
    @endif
</div>
<div class="d-flex">
    @if(isset($record->calculated['paid']))
        @if(isset($record->calculated['overpaid']))
            <div class="flex-fill">
                <div>
                    <small class="text-muted text-danger">Overpaid:</small>
                </div>
                <div>
                    {{ $record->calculated['overpaid']['value'] }}
                </div>
            </div>
        @endif
    @endif
</div>

@if($record->estimate->type === 'local' && isset($record->estimate->local->hours_min))
<div class="d-flex mt-2">
    <div class="flex-fill">
        <div>
            <small class="text-muted">Estimated time:</small>
        </div>
        <div>
            @if($record->estimate->local->hours_min === $record->estimate->local->hours_max)
                {{ $record->estimate->local->hours_min }} hrs
            @else
                {{ $record->estimate->local->hours_min }} - {{ $record->estimate->local->hours_max }} hrs
            @endif
        </div>
    </div>
</div>
@endif
