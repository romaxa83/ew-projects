@extends('cms-ui::layouts.main')

@php
    /**
     * @var $obj \WezomCms\Articles\Models\Article
     * @var $articlesListLink string
     */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div class="grid">
            <div class="gcell">
                <div>{{ $obj->published_at->format('d.m.Y') }}</div>
            </div>
        </div>
        <div class="wysiwyg js-import" data-wrap-media data-draggable-table>{!! $obj->text !!}</div>
        @widget('ui:share')
        <a href="{{ $articlesListLink }}">@lang('cms-articles::site.Назад к статьям')</a>
        @if ($prev = $obj->getPrev())
            <a href="{{ $prev->getFrontUrl() }}">@lang('cms-articles::site.Предыдущая статья')</a>
        @endif
        @if ($next = $obj->getNext())
            <a href="{{ $next->getFrontUrl() }}">@lang('cms-articles::site.Следующая статья')</a>
        @endif
    </div>
@endsection
