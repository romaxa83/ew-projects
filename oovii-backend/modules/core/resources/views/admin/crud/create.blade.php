@extends('cms-core::admin.layouts.main')

@section('hide-page-title', true)

@section('main')
    @stack('before_form')
    {!! Form::model($obj, ['route' => [$routeName . '.store', $obj->id], 'id' => 'form', 'files' => true]) !!}
        @include($viewPath . '.form')
        @widget('admin:form-buttons')
    {!! Form::close() !!}
    @stack('after_form')
@endsection
