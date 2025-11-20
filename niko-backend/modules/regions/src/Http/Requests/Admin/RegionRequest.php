<?php

namespace WezomCms\Regions\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class RegionRequest extends FormRequest
{
	use LocalizedRequestTrait;
	use RequiredIfMessageTrait;

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return $this->localizeRules(
			[
				'name' => 'required|string|max:255',
			],
			[
				'published' => 'nullable',
				'sort' => 'nullable|integer',
			]
		);
	}

	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes()
	{
		return $this->localizeAttributes(
			[
				'name' => __('cms-core::admin.layout.Name'),
			],
			[
				'sort' => __('cms-core::admin.layout.Position'),
				'published' => __('cms-core::admin.layout.Published'),
			]
		);
	}
}
