<?php

namespace App\Services;

use App\Abstractions\AbstractService;
use App\Models\Report\Feature\Feature;
use App\Models\Report\Feature\FeatureEGPivot;
use App\Models\Report\Feature\FeatureTranslation;
use App\Models\Report\Feature\FeatureValue;
use App\Models\Report\Feature\FeatureValueTranslates;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Repositories\Feature\FeatureRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeatureService extends AbstractService
{
    private $equipmentGroupRepository;
    private $featuresRepository;

    public function __construct(
        EquipmentGroupRepository $equipmentGroupRepository,
        FeatureRepository $featuresRepository
    )
    {
        parent:: __construct();
        $this->equipmentGroupRepository = $equipmentGroupRepository;
        $this->featuresRepository = $featuresRepository;
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $feature = new Feature();
            $feature->type = $data['type'];
            $feature->position = $data['position'] ?? 0;
            $feature->type_field = Feature::convertTypeFieldToDB($data['type_field']);

            // при добавлении переводов, name выступает в ввиде слага, поэтому если такой уже есть, меняем его
            $nameSlug = isset($data['name']['en']) ? Str::slug($data['name']['en']): Str::random();

            if($this->featuresRepository->getBy('name', $nameSlug)){
                $nameSlug = $nameSlug . Str::random(3);
            }

            $feature->name = $nameSlug;
            $feature->save();

            foreach ($data['name'] ?? [] as $lang => $name) {
                $t = new FeatureTranslation();
                $t->lang = $lang;
                $t->name = $name;
                $t->unit = $data['unit'][$lang] ?? null;
                $t->feature_id = $feature->id;
                $t->save();
            }

            $feature->egs()->attach($data['egs'] ?? []);
//            $feature->subEgs()->attach($data['sub_egs'] ?? []);

            DB::commit();

            return $feature;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }

    public function update(array $data, Feature $feature)
    {
        try {
            DB::beginTransaction();

            $feature->type = $data['type'];
            $feature->type_field = Feature::convertTypeFieldToDB($data['type_field']);
            $feature->position = $data['position'] ?? 0;

            $feature->save();

            foreach ($data['name'] ?? [] as $lang => $name) {
                if($feature->translations()->where('lang', $lang)->exists()){
                    $t = $feature->translations()->where('lang', $lang)->first();
                } else {
                    $t = new FeatureTranslation();
                    $t->lang = $lang;
                    $t->feature_id = $feature->id;
                }

                $t->name = $name;
                $t->unit = $data['unit'][$lang] ?? null;
                $t->save();
            }

            $feature->egs()->detach();
            $feature->egs()->attach($data['egs'] ?? []);

//            $feature->subEgs()->detach();
//            $feature->subEgs()->attach($data['sub_egs'] ?? []);

            DB::commit();

            return $feature;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }

    public function batchUpdate(array $data)
    {
        try {
            DB::beginTransaction();

            foreach ($data['features'] ?? [] as $item){
                $feature = $this->featuresRepository->getBy('id', $item['id'], ['translations']);

                if($feature){

                    foreach ($item['name'] ?? [] as $lang => $name){
                        if($feature->translations()->where('lang', $lang)->exists()){
                            $t = $feature->translations()->where('lang', $lang)->first();
                        } else {
                            $t = new FeatureTranslation();
                            $t->lang = $lang;
                            $t->feature_id = $feature->id;
                        }

                        $t->name = $name;
                        $t->unit = $item['unit'][$lang] ?? null;
                        $t->save();
                    }
                }
            }

            DB::commit();

        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());

            throw new \Exception($exception->getMessage());
        }
    }

    public function saveEgRelation($name, $featureId)
    {
        if($eg = $this->equipmentGroupRepository->getBy('name', $name)){
            $pivot = new FeatureEGPivot();
            $pivot->feature_id = $featureId;
            $pivot->eg_id = $eg->id;
            $pivot->save();
        }
    }

    public function addValue($featureId, array $data)
    {
        try {
            DB::beginTransaction();

            $value = new FeatureValue();
            $value->feature_id = $featureId;
            $value->save();

            foreach ($data as $lang => $name){
                $translate = new FeatureValueTranslates();
                $translate->lang = $lang;
                $translate->name = $name;
                $translate->value_id = $value->id;
                $translate->save();
            }
            DB::commit();

            return $value;
        } catch(\Exception $exception) {
            DB::rollBack();
            throw new \Exception($exception->getMessage());
        }
    }

    public function updateValue(FeatureValue $value, array $data)
    {
        DB::transaction(function() use ($value, $data) {
            foreach ($value->translates as $translate){
                /** @var $translate FeatureValueTranslates */
                if(isset($data[$translate->lang])){
                    $translate->name = $data[$translate->lang];
                    $translate->save();
                }
            }
        });

        return $value;
//        try {
//            DB::beginTransaction();
//            foreach ($value->translates as $translate){
//                /** @var $translate FeatureValueTranslates */
//                if(isset($data[$translate->lang])){
//                    $translate->name = $data[$translate->lang];
//                    $translate->save();
//                }
//            }
//            DB::commit();
//
//            return $value;
//        } catch(\Exception $exception) {
//            DB::rollBack();
//            throw new \Exception($exception->getMessage());
//        }
    }

    public function removeValue(FeatureValue $value): bool
    {
        return $value->delete();
    }
}
