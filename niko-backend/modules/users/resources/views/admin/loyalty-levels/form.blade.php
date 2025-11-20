<div class="row">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-group">
                    {!! Form::label(str_slug('count_auto'), __('cms-users::admin.loyalty count auto')) !!}
                    {!! Form::number('count_auto', old('count_auto', $obj->exists ?  $obj->count_auto : true))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('sum_service'), __('cms-users::admin.loyalty sum service')) !!}
                    {!! Form::number('sum_service', old('sum_service', $obj->exists ?  $obj->getSumServices() : true))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('discount_sto'), __('cms-users::admin.loyalty discount_sto')) !!}
                    {!! Form::number('discount_sto', old('discount_sto', $obj->exists ?  $obj->getDiscountSto() : true))  !!}
                </div>
                <div class="form-group">
                    {!! Form::label(str_slug('discount_spares'), __('cms-users::admin.loyalty discount_spares')) !!}
                    {!! Form::number('discount_spares', old('discount_spares', $obj->exists ?  $obj->getDiscountSpares() : true))  !!}
                </div>
            </div>
        </div>
    </div>
</div>
