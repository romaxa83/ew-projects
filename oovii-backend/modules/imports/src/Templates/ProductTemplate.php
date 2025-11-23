<?php

namespace WezomCms\Imports\Templates;

use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Catalog\Repositories\SpecificationRepository;
use WezomCms\Catalog\Repositories\SpecValueRepository;
use WezomCms\Catalog\Services\SpecValueService;
use WezomCms\Core\Models\Role;
use WezomCms\Core\Repositories\AdminRepository;

class ProductTemplate extends TemplateAbstract
{
    protected $synonyms = [
        'code' => 'group_key',
        'parent code' => 'parent',
        'название товара ru' => 'nameRu',
        'описание товара ru' => 'descRu',
        'особенности товара 1 ru' => 'featureRu1',
        'особенности товара 2 ru' => 'featureRu2',
        'особенности товара 3 ru' => 'featureRu3',
        'название товара kz' => 'nameKk',
        'описание товара kz' => 'descKk',
        'особенности товара 1 kz' => 'featureKk1',
        'особенности товара 2 kz' => 'featureKk2',
        'особенности товара 3 kz' => 'featureKk3',
        'дата публикации' => 'publishedAt',
        'стоимость без скидки' => 'cost',
        'стоимость со скидкой' => 'costDiscount',
        'дата окончания продаж' => 'expiresAt',
        'доступное колличество' => 'amount',
        'доступное количество для дного юзера' => 'amountOneUser',
        'порядковый номер' => 'sort',
        'идентификатор модератора' => 'moderatorId',
        'идентификатор поставщика' => 'providerId',
        'идентификатор коллекции' => 'collectionId',
        'вес' => 'weight',
        'цвет' => 'color',
        'размер' => 'size',
        'габариты' => 'dimension',
    ];

    public static $requiredColumns = [
        'название товара ru',
        'название товара kz',
    ];

    private AdminRepository $adminRepo;
    private CollectionRepository $collectionRepo;
    private SpecificationRepository $specificationRepo;
    private SpecValueRepository $specValueRepo;
    private SpecValueService $specValueService;

    public function __construct()
    {
        $this->adminRepo = app(AdminRepository::class);
        $this->collectionRepo = app(CollectionRepository::class);
        $this->specificationRepo = app(SpecificationRepository::class);
        $this->specValueRepo = app(SpecValueRepository::class);
        $this->specValueService = app(SpecValueService::class);
    }

    public function setParentAttribute($value): void
    {
        $this->attributes['parent'] = $this->checkValue($value);
    }

    public function setNameRuAttribute($value): void
    {
        $this->attributes['translations']['ru']['name'] = $this->checkValue($value);
    }

    public function setDescRuAttribute($value): void
    {
        $this->attributes['translations']['ru']['description'] = $this->checkValue($value);
    }

    public function setFeatureRu1Attribute($value): void
    {
        $this->attributes['translations']['ru']['feature_1'] = $this->checkValue($value);
    }

    public function setFeatureRu2Attribute($value): void
    {
        $this->attributes['translations']['ru']['feature_2'] = $this->checkValue($value);
    }

    public function setFeatureRu3Attribute($value): void
    {
        $this->attributes['translations']['ru']['feature_3'] = $this->checkValue($value);
    }

    public function setNameKkAttribute($value): void
    {
        $this->attributes['translations']['kk']['name'] = $this->checkValue($value);
    }

    public function setDescKkAttribute($value): void
    {
        $this->attributes['translations']['kk']['description'] = $this->checkValue($value);
    }

    public function setFeatureKK1Attribute($value): void
    {
        $this->attributes['translations']['kk']['feature_1'] = $this->checkValue($value);
    }

    public function setFeatureKk2Attribute($value): void
    {
        $this->attributes['translations']['kk']['feature_2'] = $this->checkValue($value);
    }

    public function setFeatureKk3Attribute($value): void
    {
        $this->attributes['translations']['kk']['feature_3'] = $this->checkValue($value);
    }

    public function setExpiresAtAttribute($value): void
    {
        $this->attributes['expiresAt'] = $this->checkValue($value);
    }

    public function setPublishedAtAttribute($value): void
    {
        $this->attributes['publishedAt'] = $this->checkValue($value);
    }

    public function setSortAttribute($value): void
    {
        $this->attributes['sort'] = $this->checkValue($value);
    }

    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = $this->checkValue($value);
    }

    public function setAmountOneUserAttribute($value): void
    {
        $this->attributes['amountOneUser'] = $this->checkValue($value);
    }

    public function setModeratorIdAttribute($value): void
    {
        $this->attributes['moderator_id'] = $this->adminRepo->existByRoleAndId($value, Role::DEFAULT_MODERATOR)
            ? $value
            : null
        ;
    }

    public function setProviderIdAttribute($value): void
    {
        $this->attributes['provider_id'] = $this->adminRepo->existByRoleAndId($value, Role::DEFAULT_PROVIDER)
            ? $value
            : null
        ;
    }

    public function setCollectionIdAttribute($value): void
    {
        $this->attributes['collection_id'] = $this->collectionRepo->existBy('id', $value)
            ? $value
            : null
        ;
    }
    // Weight
    public function setWeightAttribute($value): void
    {
        $this->forSpec($value, 'вес');
    }
    // Size
    public function setSizeAttribute($value): void
    {
        $this->forSpec($value, 'размер');
    }
    // Dimension
    public function setDimensionAttribute($value): void
    {
        $this->forSpec($value, 'габариты');
    }
    // Color
    public function setColorAttribute($value): void
    {
        $this->forSpec($value, 'цвет');
    }

    private function forSpec($value, $nameSpec)
    {
        if($value == ''){
            return;
        }

        $spec = $this->specificationRepo->getByName($nameSpec);
        if(!$spec){
            return;
        }

        $val = $this->specValueRepo->getByNameAndSpec($value, $spec->id);

        if(!$val){
            $val = $this->specValueService->createFromImport([
                "specification_id" => $spec->id,
                "value" => $value,
            ]);
        }

        $this->attributes['specifications'][] = [
            "spec_id" => $spec->id,
            "value_id" => $val->id,
        ];
    }

    public function isNotValid(): bool
    {
        if (!$this->code) {
            $this->message[] = "Invalid row for import in row = {$this->data['row_id']} , this row skipped for import";
            $this->message[] = "Invalid code - {$this->data['Code']};";
        }
        return !$this->code;
    }

    public function checkValue($value)
    {
        if($value == "NULL"){
            return null;
        }
        if(empty($value)){
            return null;
        }

        return $value;
    }
}
