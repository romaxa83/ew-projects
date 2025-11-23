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
                    <div class="form-group">
                        {!! Form::label($locale . '[text]', __('cms-orders::admin.payments.Text')) !!}
                        {!! Form::textarea($locale . '[text]', old($locale . '.text', $obj->translateOrNew($locale)->text), ['data-lang' => ($locale === 'ua' ? 'uk' : $locale)]) !!}
                    </div>
                @endLangTabs
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label('image', __('cms-orders::admin.delivery-and-payment.Image')) !!}
                    {!! Form::imageUploader('image', $obj) !!}
                </div>
            </div>
        </div>
    </div>
</div>
