<?php

namespace WezomCms\Articles\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use WezomCms\Core\Http\Requests\ChangeStatus\RequiredIfMessageTrait;
use WezomCms\Core\Traits\LocalizedRequestTrait;

class ArticleRequest extends FormRequest
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
        $notLocalized = [
            'published_at' => 'required|date'
        ];

        if (config('cms.articles.articles.use_groups')) {
            $notLocalized['article_group_id'] = 'required|exists:article_groups,id';
        }

        return $this->localizeRules(
            [
                'name' => 'nullable|string|max:255|required_if:{locale}.published,1',
                'slug' => 'nullable|string|max:255|required_if:{locale}.published,1',
                'text' => 'nullable|string|max:65535',
                'title' => 'nullable|string|max:255',
                'h1' => 'nullable|string|max:255',
                'keywords' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:255',
            ],
            $notLocalized
        );
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        $notLocalized = [
            'published_at' => __('cms-articles::admin.Published at')
        ];

        if (config('cms.articles.articles.use_groups')) {
            $notLocalized['article_group_id'] = __('cms-articles::admin.Group');
        }

        return $this->localizeAttributes(
            [
                'name' => __('cms-articles::admin.Name'),
                'published' => __('cms-core::admin.layout.Published'),
                'slug' => __('cms-core::admin.layout.Slug'),
                'text' => __('cms-articles::admin.Text'),
                'title' => __('cms-core::admin.seo.Title'),
                'h1' => __('cms-core::admin.seo.H1'),
                'keywords' => __('cms-core::admin.seo.Keywords'),
                'description' => __('cms-core::admin.seo.Description'),
            ],
            $notLocalized
        );
    }
}
