@if (isset($contact['full_name']))
    <div style="margin-bottom: 3px;"><b>{{ $contact['full_name'] }}</b></div>
@endif
@if (isset($contact['address']))
    <div style="margin-bottom: 3px;">{{ $contact['address'] }}</div>
@endif
<div style="margin-bottom: 3px;">
    @if (isset($contact['city']))
        {{ $contact['city'] }},
    @endif
    @if (isset($contact['state_short_name']))
        {{ $contact['state_short_name'] }}
    @endif
    @if (isset($contact['zip']))
        {{ $contact['zip'] }}
    @endif
</div>
@if (isset($contact['email']))
    <div style="margin-bottom: 3px;">Email: {{ $contact['email'] }}</div>
@endif
@if (isset($contact['phone']))
    <div style="margin-bottom: 3px;">Phone: {{ $contact['phone'] }}
        @if (isset($contact['phone_extension']) && $contact['phone_extension'])
            ({{ $contact['phone_extension'] }})
        @endif
    </div>
@endif
@if (isset($contact['phones']))
    @foreach ($contact['phones'] as $phone)
        @if(isset($phone['number']))
            <div style="margin-bottom: 3px;">Phone: {{ $phone['number'] }}
                @if (isset($phone['extension']) && $phone['extension'])
                    ({{ $phone['extension'] }})
                @endif
            </div>
        @endif
    @endforeach
@endif
