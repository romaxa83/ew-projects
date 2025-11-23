@php
    /**
     * @var $name string
     * @var $obj \Illuminate\Database\Eloquent\Model|\WezomCms\Core\Traits\Model\ImageAttachable|null
     * @var $deleteAction null|string
     * @var $options array
     * @var $attributes array
     */
    $locale = null;
    $field = array_get($options, 'field', $name);
    if (str_contains($name, '[')) {
        preg_match('#(.+?)\[(.+?)\]#', $name, $matched);
        $locale = array_get($matched, 1);
        $field = array_get($matched, 2);
    }

    $recommendSizes = $obj->getRecommendUploadImageSize($field);
    $fileExists = $obj->imageExists(array_get($options, 'size'), $field, $locale);
    if ($fileExists) {
        $attributes = array_merge($attributes, ['hidden']);
    }
    $attributes = array_merge(['accept' => 'image/*'], $attributes);

    if ($fileExists && !$deleteAction) {
        $deleteAction = route(
            'admin.' . app(\WezomCms\Core\Contracts\PermissionsContainerInterface::class)->getModelAbility($obj) . '.delete-image',
             ['id' => $obj->id, 'field' => $field, 'locale' => $locale]
        );
    }
@endphp
@if($recommendSizes)
    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" data-html="true"
       title="@lang('cms-core::admin.layout.The recommended size of the loaded image')<br> {{ implode('x', $recommendSizes) }}px"></i>
@endif
<div class="image-uploader clearfix js-attachable-uploader">
    @if($fileExists)
        <div class="js-attachable-preview">
            <div class="float-left" style="width: 200px;">
                <a href="{{ $obj->getExistImageUrl(\WezomCms\Core\Image\ImageService::ORIGINAL, $field, $locale) ?: $obj->withoutWebp()->getImageUrl(array_get($options, 'preview_size'), $field, $locale) }}"
                   data-fancybox class="d-block position-relative" style="padding-top: 100%; width: 100%;">
                    <img src="{{ $obj->getImageUrl(array_get($options, 'size'), $field, $locale) }}"
                         class="position-absolute"
                         style="max-width: 100%; max-height: 100%; top: 0; left: 0; right: 0; bottom: 0; margin: auto">
                </a>
            </div>
            <div class="float-left">
                <div class="mx-1 mb-1">
                    <a href="{{ $obj->getExistImageUrl(\WezomCms\Core\Image\ImageService::ORIGINAL, $field, $locale) ?: $obj->withoutWebp()->getImageUrl(array_get($options, 'download_size'), $field, $locale) }}"
                       download
                       class="btn btn-info btn-sm btn-block"><i
                            class="fa fa-download"></i> @lang('cms-core::admin.layout.Download')</a>
                </div>
                @if($deleteAction)
                    <div class="mx-1">
                        <button type="button" data-url="{{ $deleteAction }}" class="btn btn-danger btn-sm btn-block js-delete-attachable"
                                data-toggle="confirmation"
                        ><i class="fa fa-trash"></i> @lang('cms-core::admin.layout.Delete')</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
    {!! Form::file($name, $attributes) !!}
</div>
