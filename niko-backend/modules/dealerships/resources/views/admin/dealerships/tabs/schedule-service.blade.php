@php
    use WezomCms\Dealerships\Models\Schedule;
@endphp

<div class="form-group">

    @foreach(Schedule::daysForSchedule() as $day)
        <div class="row">
            {!! Form::label('monday', __('cms-dealerships::admin.schedule.' . $day), ['class' => 'col-sm-3']) !!}
            <div class="col-sm-2">
                {!! Form::label('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][work_start]', __('cms-dealerships::admin.schedule.work_start')) !!}
                {!! Form::time('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][work_start]', old('work_start', $obj->getScheduleServiceDay($day) ? $obj->getScheduleServiceDay($day)->work_start : null)) !!}
            </div>
            <div class="col-sm-2">
                {!! Form::label('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][work_end]', __('cms-dealerships::admin.schedule.work_end')) !!}
                {!! Form::time('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][work_end]', old('work_end', $obj->getScheduleServiceDay($day) ? $obj->getScheduleServiceDay($day)->work_end : null)) !!}
            </div>
            <div class="col-sm-2">
                {!! Form::label('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][break_start]', __('cms-dealerships::admin.schedule.break_start')) !!}
                {!! Form::time('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][break_start]', old('break_start', $obj->getScheduleServiceDay($day) ? $obj->getScheduleServiceDay($day)->break_start : null)) !!}
            </div>
            <div class="col-sm-2">
                {!! Form::label('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][break_end]', __('cms-dealerships::admin.schedule.break_end')) !!}
                {!! Form::time('schedule['.Schedule::TYPE_SERVICE.']['.$day.'][break_end]', old('break_end', $obj->getScheduleServiceDay($day) ? $obj->getScheduleServiceDay($day)->break_end : null)) !!}
            </div>
        </div>
    @endforeach
</div>
