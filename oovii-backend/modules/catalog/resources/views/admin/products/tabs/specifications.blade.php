<div class="row">
    @foreach($specifications as $spec)
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('SPEC_VALUES[' . $spec->id . '][]', $spec->name) !!}
                {!! Form::select(
                    'SPEC_VALUES[' . $spec->id . '][]',
                    $spec->getValuesForSelect(),
                    old('SPEC_VALUES.' . $spec->id . '.*', array_get($selectedSpecifications, $spec->id, [])),
                    ['class' => 'js-select2', 'multiple' => $spec->multiple]
                )  !!}
            </div>
        </div>
    @endforeach
</div>
