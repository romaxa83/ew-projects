@php
    /**
     * @var $row \WezomCms\Core\Settings\Fields\AbstractField|\WezomCms\Core\Settings\Fields\ValuesListContainerTrait
     * @var $id string
     * @var $name string
     * @var $value mixed
     */
@endphp
@switch($row->getType())
    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_NUMBER)
    @include('cms-core::admin.partials.fields.input-number')
    @break
    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_TEXT)
    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_INPUT)
    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_PASSWORD)
    @include('cms-core::admin.partials.fields.input-text')
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_SELECT)
    @include('cms-core::admin.partials.fields.select')
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_MULTI_SELECT)
    @include('cms-core::admin.partials.fields.multi-select')
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_TEXTAREA)
    <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $row->getRows() ?? 5 }}" {!! $row->buildAttributes() !!}
              class="form-control {{ $row->getClass() }}">{!! old($name, $value) !!}</textarea>
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_WYSIWYG)
    <textarea name="{{ $name }}" id="{{ $id }}" class="js-wysiwyg form-control {{ $row->getClass() }}"
              {!! $row->buildAttributes() !!} rows="{{ $row->getRows() ?? 10 }}">{!! old($name, $value) !!}</textarea>
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_RADIO)
    @foreach($row->getValuesList() as $listValue => $listName)
        <div class="form-check">
            <input type="radio" class="form-check-input" name="{{ $name }}" id="{{ $id . '-' . $listValue }}"
                   {!! $row->buildAttributes() !!} value="{{ $listValue }}"
                   {{ $listValue == old($name, $value) ? 'checked' : null }}>
            <label class="form-check-label" for="{{ $id . '-' . $listValue }}">{{ $listName }}</label>
        </div>
    @endforeach
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_CHECKBOX)
    @foreach($row->getValuesList() as $listValue => $listName)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="{{ $name }}[]" id="{{ $id . '-' . $listValue }}"
                   {!! $row->buildAttributes() !!} value="{{ $listValue }}"
                   {{ in_array($listValue, (array)old($name, $value)) ? 'checked' : null }}>
            <label class="form-check-label" for="{{ $id . '-' . $listValue }}">{{ $listName }}</label>
        </div>
    @endforeach
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_IMAGE)
    {!! Form::imageUploader($name, $row->getValueObj(), route($routeName . '.delete-settings-file', [$row->getStorageId(), $locale ?? null])) !!}
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_FILE)
    {!! Form::settingsFileUploader($name, $row->getValueObj(), route($routeName . '.delete-settings-file', [$row->getStorageId(), $locale ?? null])) !!}
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_SLUG)
    {!! Form::slugInput($name, old($name, $value), ['source' => $row->getSlugSourceName($locale ?? null), 'id' => $id]) !!}
    @break

    @case(\WezomCms\Core\Settings\Fields\AbstractField::TYPE_GOOGLE_MAP)
    {!! Form::map($name, old($name, $value), $row->isMultiple(), $row->height(), $row->center(), ['id' => $id]) !!}
    @break
@endswitch
