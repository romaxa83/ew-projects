<?php

use Faker\Generator as Faker;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Models\SettingTranslation;

class SettingsSeeder extends BaseSeeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
//        \DB::table('settings')->truncate();
//        \DB::table('setting_translations')->truncate();
//        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $settings = $this->settings();

        try {
            \DB::transaction(function () use ($settings) {

                foreach ($settings as $module => $group_data){
                    foreach ($group_data as $group => $key_data){
                        foreach($key_data as $key => $type_data){
                            foreach ($type_data as $type => $values)

                                $exist = Setting::where([
                                    'module' => $module,
                                    'group' => $group,
                                    'key' => $key,
                                    'type' => $type,
                                ])->exists();

                            if(!$exist){
                                $s = new Setting();
                                $s->module = $module;
                                $s->group = $group;
                                $s->key = $key;
                                $s->type = $type;
                                $s->save();

                                foreach ($values as $value => $value_data){
                                    foreach ($value_data as $lang => $val){
                                        $t = new SettingTranslation();
                                        $t->locale = $lang;
                                        $t->value = $val;
                                        $t->setting_id = $s->id;
                                        $t->save();
                                    }
                                }
                            }
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    /**
     * [
     *    'module' => [
     *      'group' => [
     *        'key' => [
     *          'type' => [
     *            'value' => [
     *              'ru' => 'some value',
     *              'uk' => 'some value'
     *            ]
     *          ]
     *        ]
     *      ]
     *    ],
     * .....
     *  ];
     */
    protected function settings()
    {
        return [
            'car' => [
                'page-settings' => [
                    'count-cars-for-user' => [
                        'number' => [
                            'value' => [
                                'ru' => '2',
                                'uk' => '2'
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}

