@extends('cms-core::admin.layouts.main')

@section('hide-page-title', true)

@section('main')
    @stack('before_form')
    {!! Form::model($obj, ['route' => [$routeName . '.update', $obj->id], 'method' => 'put', 'id' => 'form', 'files' => true]) !!}
        @include($viewPath . '.form')
        @widget('admin:form-buttons', ['btnCreateHide' => $hideCreateBtn])
    {!! Form::close() !!}
    @stack('after_form')
@endsection
