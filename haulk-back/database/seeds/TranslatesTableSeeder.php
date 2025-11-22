<?php


use App\Models\Translates\Translate;
use Illuminate\Database\Seeder;

class TranslatesTableSeeder extends Seeder
{
    public function run(): void
    {
        Illuminate\Database\Eloquent\Model::reguard();
        try {
            if (is_file(database_path('trans.json'))) {
                $jsonData = json_decode(file_get_contents(database_path('trans.json')), true);
                $arrayForWrite = [];
                foreach ($jsonData as $key1 => $value) {
                    $tmp = $value;
                    foreach ($value as $key2 => $item) {
                        if ($key2 === 'data') {
                            unset($tmp['data'], $tmp['created_at'], $tmp['updated_at']);
                            foreach ($item as $langItem) {
                                $tmp[$langItem['language']] = ['text' => Arr::get($langItem, 'text') ?? ''];
                            }
                            $arrayForWrite[] = $tmp;
                        }
                    }
                }
                foreach ($arrayForWrite as $item) {
                    $translate = Translate::where('key', '=', $item['key'])->first();
                    if ($translate && $translate->exists) {
                        continue;
                    }
                    $translateNew = new Translate();
                    $translateNew->createRow($item);
                }
                return;
            }
            return;
        } catch (Exception $exception) {
        }
    }
}
