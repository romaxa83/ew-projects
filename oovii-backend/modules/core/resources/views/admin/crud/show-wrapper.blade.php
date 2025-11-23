@extends('cms-core::admin.layouts.main')

@section('main')
    @include($viewPath . '.show')
    @widget('admin:form-buttons')
@endsection
