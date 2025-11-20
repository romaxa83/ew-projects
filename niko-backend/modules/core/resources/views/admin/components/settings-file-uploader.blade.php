@php
/**
 * @var $name string
 * @var $obj \WezomCms\Core\Models\Setting
 * @var $deleteAction string|null
 */

$field = $name;
$locale = null;
if (str_contains($name, '[')) {
    preg_match('#(.+?)\[(.+?)\]#', $name, $matched);
    $field = array_get($matched, 1);
    $locale = array_get($matched, 2);
}
@endphp
<div class="file-uploader">
    @if($obj->fileExists($locale))
        <div class="__path">{{ sprintf('%s (%s)', $obj->getFileName($locale), $obj->getFileSize(true, $locale)) }}</div>
        <div class="__controls">
            <a href="{{ $obj->getFileUrl($locale) }}" download="{{ $obj->getFileName($locale) }}" class="btn btn-info btn-sm"><i
                        class="fa fa-download"></i> @lang('cms-core::admin.layout.View / Download')</a>
            @if($deleteAction)
                <a href="{{ $deleteAction }}" class="btn btn-danger btn-sm"
                   onclick="return confirmDelete(this)"
                ><i class="fa fa-trash"></i> @lang('cms-core::admin.layout.Delete')</a>
            @endif
        </div>
    @else
        {!! Form::file($name) !!}
    @endif
</div>
