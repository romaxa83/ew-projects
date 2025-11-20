<div class="row">
	<div class="col-lg-7">
		<div class="card mb-3">
			<div class="card-header">
				<h5 class="py-2"><strong>@lang('cms-core::admin.layout.Main data')</strong></h5>
			</div>
			<div class="card-body">

                @langTabs
                <div class="form-group">
                    {!! Form::label($locale . '[name]', __('cms-core::admin.layout.Name')) !!}
                    {!! Form::text($locale . '[name]', old($locale . '.name', $obj->translateOrNew($locale)->name)) !!}
                </div>
                @endLangTabs

			</div>
		</div>
	</div>
	<div class="col-lg-5">
		<div class="card">
			<div class="card-body">
                <div class="form-group">
                    {!! Form::label(str_slug('published'), __('cms-core::admin.layout.Published')) !!}
                    {!! Form::status('published', old('published', $obj->exists ? $obj->published : true))  !!}
                </div>
			</div>
		</div>
	</div>
</div>
