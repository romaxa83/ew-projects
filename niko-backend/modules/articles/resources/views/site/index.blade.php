@extends('cms-ui::layouts.main')

@php
    /**
     * @var $result \Illuminate\Pagination\LengthAwarePaginator|\WezomCms\Articles\Models\Article[]
     */
@endphp

@section('content')
    <div class="container">
        <h1>{{ SEO::getH1() }}</h1>
        <div class="grid">
            @forelse($result as $item)
                <div class="gcell">
                    <div>
                        <div>{{ $item->published_at->format('d.m.Y') }}</div>
                        <div>
                            <a href="{{ $item->getFrontUrl() }}" title="{{ $item->name }}">
                                <img class="lozad js-import" src="{{ url('assets/images/loader.gif') }}"
                                     data-lozad="{{ $item->getImageUrl() }}" alt="{{ $item->name }}">
                            </a>
                            <a href="{{ $item->getFrontUrl() }}">{{ $item->name }}</a>
                        </div>
                        <div>{!! str_limit(strip_tags($item->text)) !!}</div>
                    </div>
                </div>
            @empty
                <div class="gcell">
                    @emptyResult
                </div>
            @endforelse
        </div>

        @if($result->hasPages())
            <div>{!! $result->links() !!}</div>
        @endif
    </div>
@endsection
