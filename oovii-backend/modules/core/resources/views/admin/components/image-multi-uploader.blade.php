@php
    /**
     * @var $model string
     */
    $recommendSizes = (new $model)->getRecommendUploadImageSize();
@endphp
<div class="dropModule">
    <div class="card dropBox">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="card-title"><i class="fa fa-download"></i> @lang('cms-core::admin.layout.Uploading images')
                @if($recommendSizes)
                    <i class="fa fa-info-circle" data-toggle="tooltip" data-placement="top" data-html="true"
                       title="@lang('cms-core::admin.layout.The recommended size of the loaded image')<br> {{ implode('x', $recommendSizes) }}px"></i>
                @endif
            </div>
            <ul class="nav flex-nowrap">
                <li class="nav-item">
                    <button type="button" class="btn btn-sm btn-outline-success dropAdd"
                            title="@lang('cms-core::admin.layout.Add images')"><i class="fa fa-plus"></i> <span class="hidden-sm-down">@lang('cms-core::admin.layout.Add images')</span></button>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn btn-sm btn-outline-info ml-2 dropLoad" title="@lang('cms-core::admin.layout.Upload all')"
                            style="display: none;"><i class="fa fa-download"></i> <span class="hidden-sm-down">@lang('cms-core::admin.layout.Upload all') (<span
                                class="dropCount">0</span>)</span></button>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2 dropCancel" title="@lang('cms-core::admin.layout.Cancel all')"
                            style="display: none;"><i
                                class="fa fa-ban"></i> <span class="hidden-sm-down">@lang('cms-core::admin.layout.Cancel all')</span></button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="dropzone" data-options="{{ json_encode([
                'params' => [
                    'model' => encrypt($model),
                    'main_id' => $id,
                    'options' => $options,
                ],
                'urls' => [
                    'save' => route('admin.image-multi-uploader.save'),
                    'getUploadedImages' => route('admin.image-multi-uploader.get-uploaded-images'),
                    'delete' => route('admin.image-multi-uploader.delete'),
                ],
                'dictDefaultMessage' => __('cms-core::admin.dropzone.Drop files here to upload'),
                'dictCancelUpload' => __('cms-core::admin.dropzone.Cancel upload'),
                'dictRemoveFile' => __('cms-core::admin.dropzone.Remove file'),
                'dictAceptUpload' => __('cms-core::admin.dropzone.Upload'),
                ]) }}"></div>
            <div class="loadedBox mt-3">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title"><i class="fa fa-file"></i> @lang('cms-core::admin.layout.Uploaded images')</h5>
                    <ul class="nav flex-nowrap">
                        <li class="nav-item">
                            <button type="button" class="btn btn-sm btn-outline-info checkAll" title="@lang('cms-core::admin.layout.Check all')" style="display: none;"><i
                                        class="fa fa-check"></i> <span class="hidden-sm-down">@lang('cms-core::admin.layout.Check all')</span></button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="btn btn-sm btn-outline-warning ml-2 uncheckAll" title="@lang('cms-core::admin.layout.Uncheck all')" style="display: none;"><i
                                        class="fa fa-ban"></i> <span class="hidden-sm-down">@lang('cms-core::admin.layout.Uncheck all')</span></button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="btn btn-sm btn-outline-danger ml-2 removeCheck" title="@lang('cms-core::admin.layout.Delete selected')" style="display: none;"><i
                                        class="fa fa-remove"></i> <span class="hidden-sm-down">@lang('cms-core::admin.layout.Delete selected')</span></button>
                        </li>
                    </ul>
                </div>
                <hr>
                <div class="dropDownload" data-options="{{ json_encode([
                    'params' => [
                        'model' => encrypt($model),
                        'main_id' => $id,
                    ],
                    'urls' => [
                        'sort' => route('admin.image-multi-uploader.sort'),
                        'setAsDefault' => route('admin.image-multi-uploader.set-as-default'),
                    ],
                    ]) }}"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit-file-name-modal-{{ sha1($model) }}" role="dialog" tabindex="-1"
         aria-labelledby="edit-file-name-modal-{{ sha1($model) }}-label" aria-hidden="true">
        <div class="modal-dialog" role="document" data-clear-body="true">
            <div class="modal-content">
                <div class="preloader"></div>
            </div>
        </div>
    </div>
</div>
