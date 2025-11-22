<?php

namespace App\Dto\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitSubType;
use App\Models\Catalog\Products\Product;
use Illuminate\Support\Str;

class ProductDto
{
    private bool $isPatch = false;
    private array $pathKeys = [];

    private ?string $guid;
    private bool $active;
    private bool $showRebate;
    private int $categoryId;
    private null|int $unitTypeId;

    private string $title;
    private string $slug;
    private ?float $seer = null;

    /** @var array<ProductTranslationDto> */
    private array $translations = [];
    private array $videoLinkIds = [];
    private array $troubleshootGroupIds = [];
    private array $certificateIds = [];
    private array $featureValues = [];
    private array $relationProducts = [];
    private array $manualIds;
    private array $labelIds;
    private array $specificationIds;

    /** @var array<ProductCertificateDto> */
    private array $certificates = [];

    private array $relativeCategoryIds;
    public ?string $unitSubType;

    public static function byArgs(
        array $args,
        bool $isPatch = false,
        array $patchKeys = [],
    ): self {
        $self = new self();

        $self->isPatch = $isPatch;
        $self->pathKeys = $patchKeys;

        $self->guid = data_get($args, 'guid');
        $self->active = data_get($args, 'active', Product::DEFAULT_ACTIVE);
        $self->showRebate= data_get($args, 'show_rebate', false);
        $self->categoryId = $args['category_id'];
        $self->title = $args['title'];
        $self->slug = Str::slug($args['slug']);
        $self->seer = data_get($args, 'seer');
        $self->videoLinkIds = data_get($args, 'video_link_ids', []);
        $self->troubleshootGroupIds = data_get($args, 'troubleshoot_group_ids', []);
        $self->certificateIds = data_get($args, 'certificate_ids', []);
        $self->manualIds = data_get($args, 'manual_ids', []);
        $self->labelIds = data_get($args, 'label_ids', []);
        $self->relationProducts = data_get($args, 'relations', []);
        $self->specificationIds = data_get($args, 'specification_ids', []);
        $self->unitTypeId = data_get($args, 'unit_type_id');
        $self->unitSubType = data_get($args, 'unit_sub_type');

        foreach ($args['translations'] ?? [] as $translation) {
            $self->translations[] = ProductTranslationDto::byArgs($translation);
        }

        foreach ($args['features'] ?? [] as $feature) {
            $self->featureValues[] = ProductFeatureDto::byArgs($feature)->getValueId();
        }

        foreach ($args['certificates'] ?? [] as $certificate) {
            $self->certificates[] = ProductCertificateDto::byArgs($certificate);
        }

        $self->relativeCategoryIds = $args['relative_category_ids'] ?? [];

        return $self;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getShowRebate(): bool
    {
        return $this->showRebate;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getSeer(): ?float
    {
        return $this->seer;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getUnitTypeId(): null|int
    {
        return $this->unitTypeId;
    }

    /**
     * @return ProductTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getVideoLinkIds(): array
    {
        return $this->videoLinkIds;
    }

    public function getTroubleshootGroupIds(): array
    {
        return $this->troubleshootGroupIds;
    }

    public function getCertificateIds(): array
    {
        return $this->certificateIds;
    }

    public function getFeatureValues(): array
    {
        return $this->featureValues;
    }

    public function getRelationProducts(): array
    {
        return $this->relationProducts;
    }

    public function getManualIds(): array
    {
        return $this->manualIds;
    }

    public function getLabelIds(): array
    {
        return $this->labelIds;
    }

    public function getSpecificationIds(): array
    {
        return $this->specificationIds;
    }

    public function getRelativeCategoryIds(): array
    {
        return $this->relativeCategoryIds;
    }

    /**
     * @return ProductCertificateDto[]
     */
    public function getCertificates(): array
    {
        return $this->certificates;
    }


    public function shouldUpdateGuid(): bool
    {
        return $this->shouldUpdateField('guid');
    }

    private function shouldUpdateField(string $field): bool
    {
        if (!$this->isPatch) {
            return true;
        }

        return in_array($field, $this->pathKeys, true);
    }

    public function shouldUpdateActive(): bool
    {
        return $this->shouldUpdateField('active');
    }

    public function shouldUpdateShowRebate(): bool
    {
        return $this->shouldUpdateField('show_rebate');
    }

    public function shouldUpdateTitle(): bool
    {
        return $this->shouldUpdateField('title');
    }

    public function shouldUpdateSlug(): bool
    {
        return $this->shouldUpdateField('slug');
    }

    public function shouldUpdateSeer(): bool
    {
        return $this->shouldUpdateField('seer');
    }

    public function shouldUpdateCategoryId(): bool
    {
        return $this->shouldUpdateField('category_id');
    }

    public function shouldUpdateUnitTypeId(): bool
    {
        return $this->shouldUpdateField('unit_type_id');
    }

    public function shouldUpdateTranslations(): bool
    {
        return $this->shouldUpdateField('translations');
    }

    public function shouldUpdateVideoLinkIds(): bool
    {
        return $this->shouldUpdateField('video_link_ids');
    }

    public function shouldUpdateTroubleshootGroupIds(): bool
    {
        return $this->shouldUpdateField('troubleshoot_group_ids');
    }

    public function shouldUpdateCertificateIds(): bool
    {
        return $this->shouldUpdateField('certificate_ids');
    }

    public function shouldUpdateFeatureValues(): bool
    {
        return $this->shouldUpdateField('features');
    }

    public function shouldUpdateRelationProducts(): bool
    {
        return $this->shouldUpdateField('relations');
    }

    public function shouldUpdateManualIds(): bool
    {
        return $this->shouldUpdateField('manual_ids');
    }

    public function shouldUpdateLabelIds(): bool
    {
        return $this->shouldUpdateField('label_ids');
    }

    public function shouldUpdateSpecificationIds(): bool
    {
        return $this->shouldUpdateField('specification_ids');
    }

    public function shouldUpdateRelativeCategoryIds(): bool
    {
        return $this->shouldUpdateField('relative_category_ids');
    }

    public function shouldUpdateCertificates(): bool
    {
        return $this->shouldUpdateField('certificates');
    }
}
