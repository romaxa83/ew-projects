<?php

namespace Tests\Unit\Models\JD;

use App\Models\JD\EquipmentGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\TestCase;

class EquipmentGroupTest extends TestCase
{
    use DatabaseTransactions;

    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function check_is_combine_false(): void
    {
        /** @var $model EquipmentGroup */
        $model = EquipmentGroup::query()->where('name', 'Seeders')->first();

        $this->assertFalse($model->isCombine());
    }

    /** @test */
    public function check_is_combine_true(): void
    {
        /** @var $model EquipmentGroup */
        $model = EquipmentGroup::query()->where('name', 'Combines')->first();

        $this->assertTrue($model->isCombine());

        /** @var $model EquipmentGroup */
        $model = EquipmentGroup::query()->where('name', 'COMBINE HEADS')->first();

        $this->assertTrue($model->isCombine());
    }

    /** @test */
    public function check_sub_features_active(): void
    {
        /** @var $model EquipmentGroup */
        $model = EquipmentGroup::query()->first();

        $f_1 = $this->featureBuilder->withTranslation()->setPosition(1)
            ->setSubEgIds($model->id)->create();
        $f_2 = $this->featureBuilder->withTranslation()->setPosition(2)
            ->setSubEgIds($model->id)->create();

        $model->refresh();

        $this->assertEquals($model->subFeaturesActive()[0]->id, $f_1->id);
        $this->assertEquals($model->subFeaturesActive()[1]->id, $f_2->id);

        $this->assertEquals($f_1->subEgs->first()->id, $model->id);
    }

    /** @test */
    public function check_sub_features_not_active(): void
    {
        /** @var $model EquipmentGroup */
        $model = EquipmentGroup::query()->first();

        $this->featureBuilder->setActive(false)->setPosition(1)
            ->setSubEgIds($model->id)->create();
        $this->featureBuilder->setActive(false)->setPosition(2)
            ->setSubEgIds($model->id)->create();

        $model->refresh();

        $this->assertNotEmpty($model->subFeatures);
        $this->assertEmpty($model->subFeaturesActive());
    }
}
