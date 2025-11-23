@extends('cms-ui::layouts.main')

@php
    /**
     * @var $obj \WezomCms\Pages\Models\Page
     */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div class="wysiwyg js-import" data-wrap-media data-draggable-table>
            {!! $obj->text !!}
        </div>
    </div>
@endsection
