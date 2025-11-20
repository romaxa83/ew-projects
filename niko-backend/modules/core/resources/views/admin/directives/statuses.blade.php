@php
    $gate = (property_exists($obj, 'abilityPrefix') ? $obj->abilityPrefix : str_replace('_', '-', $obj->getTable())) . '.edit';

    $request = $request ?? '';
    $textOn = $textOn ?? __('cms-core::admin.layout.Published');
    $textOff = $textOff ?? __('cms-core::admin.layout.Unpublished');
@endphp
@can($gate, $obj)
    <div class="btn-group js-status-switcher" data-text-on="{{ $textOn }}" data-text-off="{{ $textOff }}"
         data-model="{{ encrypt(get_class($obj)) }}" data-model-request="{{ $request ? encrypt($request) : '' }}"
         role="group">
        @if(!array_key_exists('published', $obj->getAttributes()) && method_exists($obj, 'translate'))
            @foreach(app('locales') as $locale => $language)
                @php
                    $transRow = $obj->translateOrNew($locale);
                @endphp
                <button type="button" class="btn text-nowrap btn-{{ $transRow->published ? 'success' : 'outline-secondary' }}"
                        data-locale="{{ $locale }}" data-id="{{ $obj->id }}"
                        data-status="{{ $transRow->published }}" title="{{ $language }}">{{ $locale }}</button>
            @endforeach
        @else
            <button type="button" class="btn text-nowrap btn-sm btn-{{ $obj->published ? 'success' : 'outline-secondary' }}"
                    data-id="{{ $obj->id }}" data-status="{{ $obj->published }}"
            >{!! $obj->published ? $textOn : $textOff !!}</button>
        @endif
    </div>
@else
    <div class="btn-group" role="group" aria-label="@lang('cms-core::admin.layout.Publication')">
        @if(!array_key_exists('published', $obj->getAttributes()) && method_exists($obj, 'translate'))
            @foreach(app('locales') as $locale => $language)
                @php
                    $transRow = $obj->translateOrNew($locale);
                @endphp
                <button type="button" disabled="disabled"
                        class="btn text-nowrap btn-{{ $transRow->published ? 'success' : 'outline-secondary' }}" title="{{ $language }}">{{ $locale }}</button>
            @endforeach
        @else
            <button type="button" disabled="disabled"
                    class="btn text-nowrap btn-sm btn-{{ $obj->published ? 'success' : 'outline-secondary' }}"
            >{!! $obj->published ? $textOn : $textOff !!}</button>
        @endif
    </div>
@endcan
