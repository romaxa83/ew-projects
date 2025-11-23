@extends('layouts.app')

@push('extendHeader')
    <link rel="stylesheet" media="screen, print"
          href="{{ asset('/smartadmin/css/formplugins/select2/select2.bundle.css') }}">
@endpush

@push('extendFooter')
    <script src="https://unpkg.com/popper.js@1/dist/umd/popper.min.js"></script>
    <script src="https://unpkg.com/tippy.js@4"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/select2/select2.bundle.js') }}"></script>
    <script src="{{ asset('/smartadmin/js/formplugins/bootstrap-datepicker/bootstrap-datepicker.js') }}"></script>
@endpush

@section('content')
<div id="content-spinner" class="frame-wrap position-absolute w-100 h-100 opacity-50 d-none">
    <div class="w-100 d-flex justify-content-center align-items-center">
        <div class="spinner-border text-info position-absolute" style="top:50%;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<div class="subheader">
    <h1 class="subheader-title d-flex flex-row">
        <div>
            <i class='subheader-icon fal fa-plus-circle'></i> Truck Edit
        </div>
        {{--                <div class="ml-4 fs-md d-flex flex-row">--}}
        {{--                    <div class="mr-2">--}}
        {{--                        <button class="btn btn-md btn-success waves-effect waves-themed">--}}
        {{--                            <span class="fal fa-save mr-2"></span>Save all--}}
        {{--                        </button>--}}
        {{--                    </div>--}}
        {{--                </div>--}}
    </h1>
</div>

<company-trucks></company-trucks>

<div class="modal fade" id="calendar-modal" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    Add busy time
                    {{--                            <small class="m-0 text-muted">--}}
                    {{--                                Below is a static modal example--}}
                    {{--                            </small>--}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <form>
            <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <input type="hidden" name="randomRef" value="">
                    <div class="form-group">
                        <label class="form-label">Reason</label>
                        <input type="text" class="form-control" name="reason" placeholder="Reason description">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dates</label>
                        <div class="input-group">
                            <input type="text" class="form-control dateInput" name="startDate">
                            <div class="input-group-append input-group-prepend">
                                <span class="input-group-text">to</span>
                            </div>
                            <input type="text" class="form-control dateInput" name="endDate">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time</label>
                        <div class="input-group">
                            <input type="text" class="form-control timeInput"  name="startTime" value="8:00">
                            <div class="input-group-append input-group-prepend">
                                <span class="input-group-text">to</span>
                            </div>
                            <input type="text" class="form-control timeInput"  name="endTime" value="18:00">
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" id="saveModal">Save</button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
