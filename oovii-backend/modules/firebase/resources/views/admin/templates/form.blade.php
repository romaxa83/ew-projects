<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                @langTabs
                <div class="form-group">
                    {!! Form::label($locale . '[title]', __('cms-firebase::admin.template.form.title')) !!}
                    {!! Form::text($locale . '[title]', old($locale . '.title', $obj->translateOrNew($locale)->title)) !!}
                </div>
                <div class="form-group">
                    {!! Form::label($locale . '[text]', __('cms-firebase::admin.template.form.text')) !!}
                    {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text)) !!}
                </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('active', __('cms-firebase::admin.template.form.active')) !!}
                    {!! Form::status('active') !!}
                </div>

            </div>
            <div class="card-body">
                <div class="form-group">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">{{__('cms-firebase::admin.template.form.vars')}}</li>
                        @foreach($obj->vars ?? [] as $var => $desc)
                            <li class="list-group-item">{{$var}} - {{$desc}}</li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>
