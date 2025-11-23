@extends('layouts.app')

@section('content')
<div class="subheader">
    <h1 class="subheader-title">
        Email Templates
    </h1>
</div>
<div class="row">
    <div class="col-xl-12">

        <div id="panel-1" class="panel">
            <div class="panel-hdr">
                <h2>
                    Email Templates
                </h2>
                <div class="panel-toolbar">
                    <button onclick="window.VueApp.$refs.templates.addRecord()" class="btn btn-sm btn-secondary mr-1 shadow-0 waves-effect waves-themed">
                        <i class="fal fa-plus"></i> Add Template
                    </button>
                </div>
                <div class="panel-toolbar">
                    {{--                        @include('layouts.app.helpers.table_style', ['id' => 'dt-table'])--}}
                </div>
            </div>
            <div class="panel-container show">
                <settings-email-templates ref="templates"></settings-email-templates>
            </div>
        </div>
    </div>
</div>
@endsection
