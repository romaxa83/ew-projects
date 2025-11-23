<div class="card m-auto border">
    <div class="card-header py-2 bg-primary-600">
        <div class="card-title">
            Sales Representative
        </div>
    </div>
    <div class="card-body">
        <div class="mb-2">
            <h5 class="mb-0 form-label">Name:</h5>
            <div class="text-muted">{{ $record->manager->name }}</div>
        </div>
        @if(isset($record->manager->employee->phones))
        <div class="mb-2">
            <h5 class="mb-0 form-label">Phone:</h5>
            <div class="text-muted">{{ $record->manager->employee->phones->implode('value', ', ') }}</div>
        </div>
        @endif
        <div class="mb-2">
            <h5 class="mb-0 form-label">E-mail:</h5>
            <div class="text-muted">{{ $record->manager->email }}</div>
        </div>
    </div>
</div>
