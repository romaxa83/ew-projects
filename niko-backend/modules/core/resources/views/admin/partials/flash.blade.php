@foreach (session('flash_notification', collect())->toArray() as $message)
    @if (!$message['overlay'])
        <script>
            toast({
                type: "{{ str_replace('danger', 'error', $message['level']) }}",
                title: "{{ $message['message'] }}",
                timer: {{ $message['important'] ? 100000 : 10000 }}
            });
        </script>
    @endif
@endforeach

{{ session()->forget('flash_notification') }}
