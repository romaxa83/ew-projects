<div class="card m-auto border">
    <div class="card-header py-2 bg-primary-600">
        <div class="card-title">
            Your Information
        </div>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <h5 class="mb-0 form-label">Client:</h5>
            <div class="text-muted">{{ $record->client->name .' '.$record->client->lname }}</div>
        </div>
        @if($record->client->phones->count())
            <div class="mb-2">
                <h5 class="mb-0 form-label">Phones:</h5>
                @foreach($record->client->phones as $v)
                    <div class="text-muted">{{ $v->value }}</div>
                @endforeach
            </div>
        @endif
        @if($record->client->emails->count())
            <div class="mb-2">
                <h5 class="mb-0 form-label">E-mails:</h5>
                @foreach($record->client->emails as $v)
                    <div class="text-muted">{{ $v->value }}</div>
                @endforeach
            </div>
        @endif
    </div>
</div>
