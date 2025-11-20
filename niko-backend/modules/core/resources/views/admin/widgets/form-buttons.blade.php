@php
    /**
     * @var $buttons \Illuminate\Support\Collection|\WezomCms\Core\Contracts\ButtonInterface[]
     */
@endphp
<div class="mt-3 mb-3">
    <div class="js-form-controls text-right">
        @foreach($buttons as $button)
            {!! $button->render() !!}
        @endforeach
    </div>
</div>
