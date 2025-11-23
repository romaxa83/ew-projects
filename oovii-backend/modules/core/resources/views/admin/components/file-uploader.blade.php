@php
    /**
     * @var $name string
     * @var $obj \Illuminate\Database\Eloquent\Model|\WezomCms\Core\Traits\Model\FileAttachable
     * @var $deleteAction string|null
     * @var $attributes
     */
    $locale = null;
    $field = $name;
    if (str_contains($name, '[')) {
        preg_match('#(.+?)\[(.+?)\]#', $name, $matched);
        $locale = array_get($matched, 1);
        $field = array_get($matched, 2);
    }

    $fileExists = $obj->fileExists($field, $locale);
    $fileName = $fileExists ? ($obj->getOriginalFileName($field, $locale) ?: $obj->getFileName($field, $locale)) : null;
    if ($fileExists) {
        $attributes = array_merge($attributes, ['hidden']);
    }

    if ($fileExists && !$deleteAction) {
        $deleteAction = route(
            'admin.' . app(\WezomCms\Core\Contracts\PermissionsContainerInterface::class)->getModelAbility($obj) . '.delete-file',
             ['id' => $obj->id, 'field' => $field, 'locale' => $locale]
         );
    }
@endphp
<div class="file-uploader js-attachable-uploader">
    @if($fileExists)
        <div class="js-attachable-preview">
            <div class="__path">{{ sprintf('%s (%s)', $fileName, $obj->getFileSize(true, $field, $locale)) }}</div>
            <div class="__controls">
                <a href="{{ $obj->getFileUrl($field, $locale) }}" download="{{ $fileName }}" class="btn btn-info btn-sm"><i
                        class="fa fa-download"></i> @lang('cms-core::admin.layout.View / Download')</a>
                @if($deleteAction)
                    <button type="button"
                            data-url="{{ $deleteAction }}"
                            class="btn btn-danger btn-sm js-delete-attachable"
                            data-toggle="confirmation"
                    ><i class="fa fa-trash"></i> @lang('cms-core::admin.layout.Delete')</button>
                @endif
            </div>
        </div>
    @endif
    {!! Form::file($name, $attributes) !!}
</div>
