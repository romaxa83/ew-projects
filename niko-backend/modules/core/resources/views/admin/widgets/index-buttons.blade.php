@php
    /**
     * @var $buttons \Illuminate\Support\Collection|\WezomCms\Core\Contracts\ButtonInterface[]
     */
@endphp
<div>
    @foreach($buttons as $button)
        {!! $button->render() !!}
    @endforeach
</div>
