@php
    /**
     * @var $name string
     * @var $obj \WezomCms\Core\Models\Setting
     * @var $deleteAction string|null
     * @var $attributes array
     */

    $field = $name;
    $locale = null;
    if (str_contains($name, '[')) {
        preg_match('#(.+?)\[(.+?)\]#', $name, $matched);
        $field = array_get($matched, 1);
        $locale = array_get($matched, 2);
    }

    $fileExists = $obj->fileExists($locale);
    if ($fileExists) {
        $attributes = array_merge($attributes, ['hidden']);
    }
@endphp
<div class="file-uploader js-attachable-uploader">
    @if($fileExists)
        <div class="js-attachable-preview">
            <div class="__path">{{ sprintf('%s (%s)', $obj->getFileName($locale), $obj->getFileSize(true, $locale)) }}</div>
            <div class="__controls">
                <a href="{{ $obj->getFileUrl($locale) }}" download="{{ $obj->getFileName($locale) }}" class="btn btn-info btn-sm"><i
                            class="fa fa-download"></i> @lang('cms-core::admin.layout.View / Download')</a>
                @if($deleteAction)
                    <button type="button" data-url="{{ $deleteAction }}" class="btn btn-danger btn-sm js-delete-attachable"
                            data-toggle="confirmation"
                    ><i class="fa fa-trash"></i> @lang('cms-core::admin.layout.Delete')</button>
                @endif
            </div>
        </div>
    @endif
    {!! Form::file($name, $attributes) !!}
</div>
