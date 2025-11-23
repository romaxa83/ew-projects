@extends('layouts.app')


@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        Routes 2 Roles
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">
        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    Routes 2 Roles
                </h2>
                <div class="panel-toolbar">
{{--                        @include('layouts.app.helpers.table_style', ['id' => 'dt-table'])--}}
                </div>
            </div>
            <div class="panel-container show">
                <div class="panel-content">

                    <settings-route-list></settings-route-list>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
