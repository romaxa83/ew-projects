<div class="card filter-card mb-3 collapse {{ $expanded ? ' show' : '' }}" id="filter-body">
    <div class="card-body pb-0">
        {!! Form::open(['route' => $currentRouteName, 'class' => 'js-filter-form', 'method' => 'GET']) !!}
        <input type="hidden" name="filter_form" value="1">
        <div class="row">
            @foreach($fields as $field)
                @php
                    /** @var $field \WezomCms\Core\Contracts\Filter\FilterFieldInterface */
                @endphp
                @if($field->isHide() === true)
                    <input type="hidden" name="{{ $field->getName() }}"
                           value="{{ array_get($values, $field->getName()) }}">
                @else
                    <div class="col-xs-12 col-sm-6 col-lg-{{ $field->getSize() ?: 3 }}">
                        <div class="form-group">
                            @switch($field->getType())
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_SELECT_WITH_CUSTOM_OPTIONS)
                                <select name="{{ $field->getName() }}" id="{{ $field->getName() }}"
                                        class="form-control {{ $field->getClass() }}" style="width: 100%">
                                    {!! $field->getCustomOptions() !!}
                                </select>
                                @break
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_SELECT)
                                @if(array_get($field->getAttributes(), 'multiple'))
                                    @php
                                    $options = $field->getOptions();
                                    $optionAttributes = [];
                                    if (!\Illuminate\Support\Str::contains($field->getClass(), ['js-select2', 'js-ajax-select2'])) {
                                        $options = ['' => $field->getPlaceholder()] + $options;
                                        $optionAttributes = ['' => ['disabled' => true, 'selected' => false]];
                                    }
                                    @endphp
                                    {!! Form::select($field->getName(), $options, array_get($values, \WezomCms\Core\Foundation\Helpers::convertFieldToDot(preg_replace('/\[\]$/', '', $field->getName()))), ['class' => $field->getClass(), 'data-placeholder' => $field->getPlaceholder(), 'style' => 'width: 100%'] + $field->getAttributes(), $optionAttributes) !!}
                                @else
                                    {!! Form::select($field->getName(), $field->getOptions(), array_get($values, $field->getName()), ['class' => $field->getClass(), 'placeholder' => $field->getPlaceholder(), 'style' => 'width: 100%'] + $field->getAttributes()) !!}
                                @endif
                                @break
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_NUMBER)
                                {!! Form::number($field->getName(), array_get($values, $field->getName()), ['class' => $field->getClass(), 'step' => str_replace(',', '.', $field->getStep()), 'placeholder' => $field->getPlaceholder()] + $field->getAttributes()) !!}
                                @break
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_RANGE)
                                <div class="input-group">
                                    {!! Form::number($field->getName() . '_from', array_get($values, $field->getName() . '_from'), ['class' => $field->getClass(), 'step' => str_replace(',', '.', $field->getStep()), 'autocomplete' => 'off', 'placeholder' => $field->getPlaceholderFrom(), 'title' => $field->getPlaceholderFrom()] + $field->getAttributes()) !!}
                                    <div class="input-group-append">
                                        <div class="input-group-text"> -</div>
                                    </div>
                                    {!! Form::number($field->getName() . '_to', array_get($values, $field->getName() . '_to'), ['class' => $field->getClass(), 'step' => str_replace(',', '.', $field->getStep()), 'autocomplete' => 'off', 'placeholder' => $field->getPlaceholderTo(), 'title' => $field->getPlaceholderTo()] + $field->getAttributes()) !!}
                                </div>
                                @break
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_DATE_RANGE)
                                <div class="input-group input-daterange js-datepicker">
                                    {!! Form::text($field->getName() . '_from', array_get($values, $field->getName() . '_from'), ['class' => $field->getClass(), 'autocomplete' => 'off', 'placeholder' => $field->getPlaceholderFrom(), 'title' => $field->getPlaceholderFrom()] + $field->getAttributes()) !!}
                                    <div class="input-group-append">
                                        <div class="input-group-text">-</div>
                                    </div>
                                    {!! Form::text($field->getName() . '_to', array_get($values, $field->getName() . '_to'), ['class' => $field->getClass(), 'autocomplete' => 'off', 'placeholder' => $field->getPlaceholderTo(), 'title' => $field->getPlaceholderTo()] + $field->getAttributes()) !!}
                                </div>
                                @break
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_DATE_TIME_RANGE)
                                {!! Form::text($field->getName(), array_get($values, $field->getName()), ['class' => 'js-datetimerangepicker ' . $field->getClass(), 'autocomplete' => 'off', 'placeholder' => $field->getPlaceholder()] + $field->getAttributes()) !!}
                                @break
                                @case(\WezomCms\Core\Contracts\Filter\FilterFieldInterface::TYPE_INPUT)
                                @default
                                {!! Form::text($field->getName(), array_get($values, $field->getName()), ['class' => $field->getClass(), 'placeholder' => $field->getPlaceholder()] + $field->getAttributes()) !!}
                                @break
                            @endswitch
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="col-xs-12 col-sm-6 col-lg-{{ array_get($config, 'control_size', 3) }} ">
                <div class="filter-control-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i>&nbsp;@lang('cms-core::admin.filter.Apply')</button>
                    <a href="{{ $resetUrl }}"
                       class="btn btn-outline-secondary"
                       title="@lang('cms-core::admin.filter.Reset')"
                       data-toggle="tooltip"><i class="fa fa-eraser"></i></a>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>
