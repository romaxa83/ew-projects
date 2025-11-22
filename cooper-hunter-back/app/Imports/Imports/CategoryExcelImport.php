<?php

namespace App\Imports\Imports;

use App\Models\Catalog\Categories\Category;
use App\Models\Localization\Language;
use App\Services\Catalog\Categories\CategoryService;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Throwable;

class CategoryExcelImport implements ToCollection, WithChunkReading
{

    public function __construct(private CategoryService $service)
    {
    }

    public function collection(Collection $rows): void
    {
        try {
            foreach ($this->preData($rows) as $k => $item){
//                if($k == 79){

                    $data = [];

                    if(strripos($item['parent'], ':')){
                        $temp = explode(':', $item['parent']);
                        logger("{$k} - ITEM_NAME - {$item['parent']}");

                        $parent = Category::query()
                            ->with('translations')
                            ->whereHas('translations', function ($q) use ($temp) {
                                $q->where('language', 'en')->where('title', $temp[0]);
                            })
                            ->get();
                        logger("{$k} - TEMP_O - {$temp[0]}");

                        $data['parent_id'] =  $parent->isNotEmpty() ? $parent[0]->id : null;
                        if($parent->count() > 1){

                            $parentPP = null;
                            if(isset($temp[2])){
                                $parentPP = Category::query()
                                    ->with('translations')
                                    ->whereHas('translations', function ($q) use ($temp) {
                                        $q->where('language', 'en')->where('title', $temp[2]);
                                    })
                                    ->first();
                            }

                            $parentP = Category::query()
                                ->with('translations')
                                ->whereHas('translations', function ($q) use ($temp) {
                                    $q->where('language', 'en')->where('title', $temp[1]);
                                });
                            if(null !== $parentPP){
                                $parentP->where('parent_id', $parentPP->id);
                            }
                            $parentP = $parentP->first();

                            $parent = Category::query()
                                ->with('translations')
                                ->whereHas('translations', function ($q) use ($temp, $parentP) {
                                    $q->where('language', 'en')
                                        ->where('title', $temp[0]);
                                })
                                ->where('parent_id', $parentP->id)
                                ->first();

                            $data['parent_id'] = $parent->id ?? null;
                        }
                    } else {
                        $parent = Category::query()
                            ->with('translations')
                            ->whereHas('translations', function ($q) use ($item) {
                                $q->where('language', 'en')->where('title', $item['parent']);
                            })->first();


                        $data['parent_id'] = $parent->id ?? null;
                    }
            }

            $count = 0;
            foreach (Language::list() as $key => $lang) {
                $data['translations'][$count]['lang'] = $key;
                $data['translations'][$count]['title'] = $item['name'];
                if ($key !== 'en') {
                    $data['translations'][$count]['title'] .= " __(Translates into {$lang})";
                }
                $count++;
//                }
//                $dto = CategoryDto::byArgs($data);
//                $this->service->create($dto);


            }
        } catch (Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function chunkSize(): int
    {
        return 200;
    }

    private function preData(Collection $rows): array
    {
        $data = [];
        foreach ($rows as $key => $row) {
            $data = array_values($data);
            if ($key !== 0) {
                foreach ($row as $k => $title) {
                    if(!is_null($title)){
                        if(!str_starts_with($title, 'List of models') ){
                            for($i = (count($data) -1); $i > -1; $i--){
                                $last = $k - 1;
                                if(isset($data[$i]['depth']) && ($data[$i]['depth'] == $last)){
                                    $data[$key]['parent'] = $data[$i]['name'];
                                    if(isset($data[$i]['parent'])){
                                        $data[$key]['parent'] .= ':' . $data[$i]['parent'];
                                    }
                                    break;
                                }
                            }
                            $data[$key]['name'] = $title;
                            $data[$key]['depth'] = $k;
                        }
                    }
                }
            }
        }
        return $data;
    }
}
