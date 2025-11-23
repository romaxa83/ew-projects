<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                @langTabs
                <div class="form-group">
                    {!! Form::label($locale . '[name]', __('cms-orders::admin.statuses.Name')) !!}
                    {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label($locale . '[notification_text]', __('cms-orders::admin.statuses.Notification text')) !!}
                    {!! Form::textarea(
                        $locale . '[notification_text]',
                        old($locale . '.notification_text', $obj->translateOrNew($locale)->notification_text)
                    )!!}
                </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('color', __('cms-orders::admin.statuses.Color')) !!}
                    {!! Form::color('color', old('color', $obj->color), [ 'style' => 'width: 100px;' ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('amocrm_value_id', __('cms-branches::admin.Amo value id')) !!}
                    {!! Form::text('amocrm_value_id') !!}
                </div>
            </div>
        </div>
    </div>
</div>
