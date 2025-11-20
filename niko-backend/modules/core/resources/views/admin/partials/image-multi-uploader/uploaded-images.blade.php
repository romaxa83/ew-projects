@foreach($images as $image)
    <div class="loadedBlock" data-image="{{ $image->id }}">
        <div class="loadedImage">
            <img src="{{ $image->getImageUrl(array_get($options, 'thumbnail_size')) }}"/>
        </div>

        <div class="loadedControl">
{{--            @if(array_get($options, 'default_image', true))--}}
{{--                <div class="loadedCtrl loadedCover">--}}
{{--                    <label>--}}
{{--                        <input id="def-img-{{ $image->id }}" name="default_image" value="{{ $image->id }}"--}}
{{--                               type="radio" {{ $image->default ? 'checked="checked"' : '' }}/>--}}
{{--                        <ins></ins>--}}
{{--                        <span class="btn btn-success" title="@lang('cms-core::admin.layout.Cover')"><i--}}
{{--                                    class="fa fa-bookmark-o"></i></span>--}}
{{--                        <div class="checkCover"></div>--}}
{{--                    </label>--}}
{{--                </div>--}}
{{--            @endif--}}
            <div class="loadedCtrl loadedCheck">
                <label>
                    <input type="checkbox">
                    <ins></ins>
                    <span class="btn btn-info" title="@lang('cms-core::admin.layout.Check')"><i class="fa fa-check"></i></span>
                    <div class="checkInfo"></div>
                </label>
            </div>
            <div class="loadedCtrl loadedView">
                <a href="{{ $image->getImageUrl(array_get($options, 'preview_size')) }}"
                   class="btn btn-primary btnImage" title="@lang('cms-core::admin.layout.Browsing')"
                ><i class="fa fa-search-plus"></i></a>
            </div>
            @if($image->renamePopup())
                <div class="loadedCtrl loadedView">
                    <button data-toggle="modal" data-target="#edit-file-name-modal-{{ sha1(get_class($model)) }}"
                            data-remote="{{ route('admin.image-multi-uploader.rename-form', [$image->id, 'model' => encrypt($model)]) }}"
                            type="button" class="btn btn-success btn-sm"
                            title="@lang('cms-core::admin.layout.Rename')">
                        <i class="fa fa fa-pencil"></i></button>
                </div>
            @endif
            <div class="loadedCtrl loadedDelete">
                <button type="button" class="btn btn-danger" data-id="{{ $image->id }}"
                        title="@lang('cms-core::admin.layout.Delete')"><i class="fa fa-remove"></i></button>
            </div>
        </div>
        <div class="loadedDrag"></div>
    </div>
@endforeach
