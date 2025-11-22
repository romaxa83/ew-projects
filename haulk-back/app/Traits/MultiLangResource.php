<?php


namespace App\Traits;


trait MultiLangResource
{
    /**
     * @param array $data
     * @param $resource
     * @return array
     */
    public function mergeMultiLangData(array $data, $resource)
    {
        foreach (config('languages') as $language) {
            foreach ($resource->data as $translatedData) {
                foreach ($translatedData->getFillable() as $value) {
                    $data[$language['slug']] = [$value => $resource->dataFor($language['slug'])->{$value}];
                }
            }
        }
        return $data;
    }
}
