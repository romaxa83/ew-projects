@extends('cms-core::admin.layouts.main')

@section('main')
    <div class="row">
        @foreach($widgets as $widget)
            {!! $widget->toHtml() !!}
        @endforeach
    </div>
@endsection
