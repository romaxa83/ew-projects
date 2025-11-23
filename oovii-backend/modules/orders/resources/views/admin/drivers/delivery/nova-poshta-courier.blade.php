@php
    /**
     * @var $storage \WezomCms\Orders\Models\OrderDeliveryInformation
     */
@endphp
<div class="row">
    <div class="col-md-9">
        {!! Form::label('deliveryInformation[city]', __('cms-orders::admin.courier.Locality')) !!}
        {!! Form::text('deliveryInformation[city]', old('deliveryInformation.city', $storage->city)) !!}
    </div>
    <div class="col-md-3">
        {!! Form::label('deliveryInformation[ttn]', __('cms-orders::admin.orders.TTN')) !!}
        {!! Form::text('deliveryInformation[ttn]', old('deliveryInformation.ttn', $storage->ttn)) !!}
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('deliveryInformation[street]', __('cms-orders::admin.courier.Street')) !!}
            {!! Form::text('deliveryInformation[street]', old('deliveryInformation.street', $storage->street)) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('deliveryInformation[house]', __('cms-orders::admin.courier.House')) !!}
            {!! Form::text('deliveryInformation[house]', old('deliveryInformation.house', $storage->house)) !!}
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('deliveryInformation[room]', __('cms-orders::admin.courier.Room')) !!}
            {!! Form::text('deliveryInformation[room]', old('deliveryInformation.room', $storage->room)) !!}
        </div>
    </div>
</div>
