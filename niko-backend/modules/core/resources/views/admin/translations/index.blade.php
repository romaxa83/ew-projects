@extends('cms-core::admin.crud.index')

@php
    /**
     * @var $translations \Illuminate\Database\Eloquent\Collection|\WezomCms\Core\Models\Translation[]
     */
@endphp

@section('content')
    <div class="m-3">
        <table id="dataTable" class="table table-bordered table-hover" v-pre>
            <thead>
            <tr>
                <th>@lang('cms-core::admin.translations.Keys')</th>
                @foreach($locales as $locale => $language)
                    <th>{{ $language }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($translations as $fullKey => $translation)
                <tr>
                    <td>{{ $fullKey }}</td>
                    @foreach($locales as $locale => $language)
                        <td data-id="{{ array_get($translation, "$locale.id") }}">
                            {{ array_get($translation, "$locale.text") }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
