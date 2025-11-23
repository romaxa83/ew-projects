<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('published', __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published') !!}
                </div>
                @langTabs
                    <div class="form-group">
                        {!! Form::label($locale . '[name]', __('cms-orders::admin.payments.Name')) !!}
                        {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name))  !!}
                    </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('PAYMENTS[]', __('cms-orders::admin.deliveries.Payments')) !!}
                    {!! Form::select('PAYMENTS[]', $payments, old('PAYMENTS', $selectedPayments), ['multiple' => 'multiple', 'class' => 'js-select2']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
