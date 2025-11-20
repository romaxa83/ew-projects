<?php

namespace Tests\Unit\Models\Report\Feature;

use App\Models\Report\Feature\Feature;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use DatabaseTransactions;

    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function check_feature_is_crop(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->setTypeFeature(Feature::TYPE_FEATURE_CROP)
            ->withTranslation()
            ->create();

        $this->assertTrue($model->isCrop());
    }

    /** @test */
    public function check_feature_is_not_crop(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->create();

        $this->assertFalse($model->isCrop());
    }

    /** @test */
    public function check_feature_for_ground(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->setType(Feature::TYPE_GROUND)
            ->create();

        $this->assertTrue($model->forGround());
        $this->assertFalse($model->forMachine());
    }

    /** @test */
    public function check_feature_for_machine(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->setType(Feature::TYPE_MACHINE)
            ->create();

        $this->assertFalse($model->forGround());
        $this->assertTrue($model->forMachine());
    }

    /** @test */
    public function check_type_field_for_front_string(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_STRING)
            ->create();

        $this->assertEquals($model->type_field, Feature::TYPE_FIELD_STRING);
        $this->assertEquals($model->type_field_for_front, Feature::TYPE_FIELD_STRING_FOR_FRONT);
    }

    /** @test */
    public function check_type_field_for_front_int(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_INT)
            ->create();

        $this->assertEquals($model->type_field, Feature::TYPE_FIELD_INT);
        $this->assertEquals($model->type_field_for_front, Feature::TYPE_FIELD_INT_FOR_FRONT);
    }

    /** @test */
    public function check_type_field_for_front_bool(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_BOOL)
            ->create();

        $this->assertEquals($model->type_field, Feature::TYPE_FIELD_BOOL);
        $this->assertEquals($model->type_field_for_front, Feature::TYPE_FIELD_BOOL_FOR_FRONT);
    }

    /** @test */
    public function check_type_field_for_front_select(): void
    {
        /** @var $model Feature */
        $model = $this->featureBuilder
            ->withTranslation()
            ->setTypeField(Feature::TYPE_FIELD_SELECT)
            ->create();

        $this->assertEquals($model->type_field, Feature::TYPE_FIELD_SELECT);
        $this->assertEquals($model->type_field_for_front, Feature::TYPE_FIELD_SELECT_FOR_FRONT);
    }

    /** @test */
    public function check_convert_type_field_to_db(): void
    {
        $this->assertEquals(Feature::convertTypeFieldToDB(Feature::TYPE_FIELD_STRING_FOR_FRONT), Feature::TYPE_FIELD_STRING);
        $this->assertEquals(Feature::convertTypeFieldToDB(Feature::TYPE_FIELD_INT_FOR_FRONT), Feature::TYPE_FIELD_INT);
        $this->assertEquals(Feature::convertTypeFieldToDB(Feature::TYPE_FIELD_BOOL_FOR_FRONT), Feature::TYPE_FIELD_BOOL);
        $this->assertEquals(Feature::convertTypeFieldToDB(Feature::TYPE_FIELD_SELECT_FOR_FRONT), Feature::TYPE_FIELD_SELECT);
    }
}
