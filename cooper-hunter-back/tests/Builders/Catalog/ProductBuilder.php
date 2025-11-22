<?php

namespace Tests\Builders\Catalog;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Enums\Catalog\Products\ProductUnitSubType;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Labels\Label;
use App\Models\Catalog\Manuals\Manual;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductFeatureValue;
use App\Models\Catalog\Videos\VideoLink;
use Database\Factories\Catalog\Products\ProductTranslationFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Builders\Catalog\Troubleshoots\TroubleshootBuilder;
use Tests\Builders\Catalog\Video\LinkBuilder;

class ProductBuilder
{
    private int|null $id = null;
    private string|null $guid = null;
    private bool $active = Product::DEFAULT_ACTIVE;
    private int $sort = Product::DEFAULT_SORT;
    private null|int $categoryId;
    private null|int $unitTypeId = null;
    private array $media = [];

    private array $videoLinkIds = [];
    private array $manualIds = [];
    private array $labelIds = [];
    private array $certificateIds = [];
    private array $relationIds = [];
    private array $features = [];

    private null|string $title = null;
    private null|string $slug = null;
    private null|string $description = null;
    private null|ProductUnitSubType $unitSubType = null;
    private string $ownerType = ProductOwnerType::COOPER;

    private bool $withTranslation = false;
    private bool $withVideoLinks = false;
    private bool $withTroubleshoots = false;
    private bool $withRelations = false;
    private bool $withCertificates = false;

    public function setFeatureValue(array $payload): self
    {
        $this->features = $payload;

        return $this;
    }

    public function setOwnerType(string $value): self
    {
        $this->ownerType = $value;

        return $this;
    }

    public function setMedia(...$values): self
    {
        $media = [];
        foreach ($values as $k => $value){
            $media[$k] = $value;
        }
//        "https://api.olmo.wezom.agency/storage/23/3164377765.jpg"
        $this->media = [
            "media" => $media
        ];

        return $this;
    }

    public function setGuid(string $value): self
    {
        $this->guid = $value;

        return $this;
    }

    public function withTranslation(): self
    {
        $this->withTranslation = true;

        return $this;
    }

    public function withVideoLinks(): self
    {
        $this->withVideoLinks = true;

        return $this;
    }

    public function setVideoLinks(VideoLink ...$models): self
    {
        foreach ($models as $model){
            $this->videoLinkIds[] = $model->id;
        }

        return $this;
    }

    public function setManuals(Manual ...$models): self
    {
        foreach ($models as $model){
            $this->manualIds[] = $model->id;
        }

        return $this;
    }

    public function setLabels(Label ...$models): self
    {
        foreach ($models as $model){
            $this->labelIds[] = $model->id;
        }

        return $this;
    }

    public function withTroubleshoots(): self
    {
        $this->withTroubleshoots = true;

        return $this;
    }

    public function withCertificates(): self
    {
        $this->withCertificates = true;

        return $this;
    }

    public function withRelations(): self
    {
        $this->withRelations = true;

        return $this;
    }

    public function setRelations(Product ...$products): self
    {
        foreach ($products as $product){
            $this->relationIds[] = $product->id;
        }

        return $this;
    }

    public function create(): Product
    {
        $model = $this->save();

        if ($this->withTranslation) {
            $this->saveEnTranslation($model->id);
            $this->saveEsTranslation($model->id);
        }

        if ($this->withTroubleshoots) {
            $this->troubleshootIds = $this->troubleshootIds();
        }

        if ($this->withVideoLinks) {
            $this->videoLinkIds = $this->videoLinkIds();
        }

        if ($this->withRelations) {
            $this->relationIds = $this->relationIds();
        }

        if ($this->withCertificates) {
            $this->certificateIds = $this->certificateIds();
        }

        if (!empty($this->features)) {
            foreach ($this->features as $value) {
                DB::table(ProductFeatureValue::TABLE)->insert([
                    'product_id' => $model->id,
                    'value_id' => $value,
                ]);
            }
        }

        $model->videoLinks()->attach($this->videoLinkIds);
        $model->manuals()->attach($this->manualIds);
        $model->labels()->attach($this->labelIds);
        $model->relationProducts()->attach($this->relationIds);
        $model->certificates()->attach($this->certificateIds);

        $this->clear();

        return $model;
    }

    private function save(): Product
    {
        $data = [
            'category_id' => $this->getCategoryId(),
            'unit_type_id' => $this->unitTypeId,
            'active' => $this->getActive(),
            'title_metaphone' => makeSearchSlug($this->getTitle()),
            'owner_type' => $this->ownerType,
            'unit_sub_type' => $this->unitSubType,
        ];

        if ($this->getId()) {
            $data['id'] = $this->getId();
        }
        if($this->guid){
            $data['guid'] = $this->guid;
        }
        if(!empty($this->media)){
            $data['olmo_additions'] = $this->media;
        }

        return Product::factory()->new($data)->create();
    }

    public function getCategoryId(): int
    {
        return $this->categoryId ?? $this->getCategoryRandom()->id;
    }

    public function setCategoryId(int $id): self
    {
        $this->categoryId = $id;

        return $this;
    }

    public function setUnitTypeId(int $id): self
    {
        $this->unitTypeId = $id;

        return $this;
    }

    public function setSubUnitType(ProductUnitSubType $value): self
    {
        $this->unitSubType = $value;

        return $this;
    }

    private function getCategoryRandom(): Category
    {
        return app(CategoryBuilder::class)->create();
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    private function saveEnTranslation($modelId): void
    {
        ProductTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'en',
            'description' => $this->getDescription(),
        ])
            ->create();
    }

    public function getDescription(): ?string
    {
        if (null === $this->description) {
            $this->setDescription(Str::random(100));
        }

        return $this->description;
    }

    public function setDescription(string $desc): self
    {
        $this->description = $desc;

        return $this;
    }

    private function saveEsTranslation($modelId): void
    {
        ProductTranslationFactory::new([
            'row_id' => $modelId,
            'language' => 'es',
            'description' => $this->getDescription() . ' (ES)',
        ])
            ->create();
    }

    private function troubleshootIds(): array
    {
        $builder = app(TroubleshootBuilder::class);

        return [
            $builder->create()->id,
            $builder->create()->id,
        ];
    }

    private function videoLinkIds(): array
    {
        $builder = app(LinkBuilder::class);

        return [
            $builder->create()->id,
            $builder->create()->id,
        ];
    }

    private function relationIds(): array
    {
        return [
            Product::factory()->create()->id,
            Product::factory()->create()->id,
        ];
    }

    private function certificateIds(): array
    {
        return [
            Certificate::factory()->create()->id,
            Certificate::factory()->create()->id,
        ];
    }

    private function clear(): void
    {
        $this->active = Product::DEFAULT_ACTIVE;
        $this->sort = Product::DEFAULT_SORT;
        $this->id = null;
        $this->guid = null;
        $this->categoryId = null;
        $this->unitTypeId = null;
        $this->unitSubType = null;

        $this->videoLinkIds = [];
        $this->manualsIds = [];
        $this->labelsIds = [];
        $this->troubleshootIds = [];
        $this->relationIds = [];
        $this->certificateIds = [];
        $this->features = [];

        $this->withTranslation = false;
        $this->withVideoLinks = false;
        $this->withTroubleshoots = false;
        $this->withRelations = false;
        $this->withCertificates = false;

        $this->title = null;
        $this->description = null;
        $this->ownerType = ProductOwnerType::COOPER;
        $this->media = [];
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getTitle(): ?string
    {
        if (null === $this->title) {
            $this->setTitle(Str::random(10));
        }

        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        if (null === $this->slug) {
            $this->setSlug(Str::random(10));
        }

        return $this->title;
    }

    public function setSlug(string $title): self
    {
        $this->slug = $title;

        return $this;
    }
}
