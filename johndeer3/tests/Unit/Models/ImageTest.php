<?php

namespace Tests\Unit\Models;

use App\Models\Image;
use App\Models\Report\Report;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\ImageBuilder;
use Tests\Builder\Report\ReportBuilder;
use Tests\TestCase;
use Tests\Builder\UserBuilder;

class ImageTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $imageBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->imageBuilder = resolve(ImageBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function check_get_coords(): void
    {
        $user = $this->userBuilder->create();
        $rep = $this->reportBuilder->setUser($user)->create();

        $cords = [
            "lat" => 2.99,
            "lon" => 77.99,
        ];

        /** @var $model Image */
        $model = $this->imageBuilder->setEntity($rep)->setData($cords)->create();

        $this->assertEquals($model->getCoords(), $cords);
    }

    /** @test */
    public function check_get_coords_empty(): void
    {
        $user = $this->userBuilder->create();
        $rep = $this->reportBuilder->setUser($user)->create();

        $cords = [];

        /** @var $model Image */
        $model = $this->imageBuilder->setEntity($rep)->setData($cords)->create();

        $this->assertNull($model->getCoords());
    }

    /** @test */
    public function check_entity_relation(): void
    {
        $user = $this->userBuilder->create();
        $rep = $this->reportBuilder->setUser($user)->create();

        /** @var $model Image */
        $model = $this->imageBuilder->setEntity($rep)->create();

        $this->assertTrue($model->entity instanceof Report);
        $this->assertEquals($model->entity->id, $rep->id);
    }

    /** @test */
    public function check_format_array(): void
    {
        $user = $this->userBuilder->create();
        $rep = $this->reportBuilder->setUser($user)->create();

        $data = [
            $i_1 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::WORKING_START])->create(),
            $i_2 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::WORKING_START])->create(),
            $i_3 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::WORKING_END])->create(),
            $i_4 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::WORKING_END])->create(),
            $i_5 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::EQUIPMENT])->create(),
            $i_6 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::ME])->create(),
            $i_7 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::OTHERS])->create(),
            $i_8 = $this->imageBuilder->setEntity($rep)->setData(['model' => Image::SIGNATURE])->create(),
        ];

        $d = Image::formatArray($data);

        $this->assertCount(2, data_get($d, Image::WORKING_START));
        $this->assertEquals($i_1->id, $d[Image::WORKING_START][0]->id);
        $this->assertEquals($i_2->id, $d[Image::WORKING_START][1]->id);

        $this->assertCount(2, data_get($d, Image::WORKING_END));
        $this->assertEquals($i_3->id, $d[Image::WORKING_END][0]->id);
        $this->assertEquals($i_4->id, $d[Image::WORKING_END][1]->id);

        $this->assertCount(1, data_get($d, Image::EQUIPMENT));
        $this->assertEquals($i_5->id, $d[Image::EQUIPMENT][0]->id);

        $this->assertCount(1, data_get($d, Image::ME));
        $this->assertEquals($i_6->id, $d[Image::ME][0]->id);

        $this->assertCount(1, data_get($d, Image::OTHERS));
        $this->assertEquals($i_7->id, $d[Image::OTHERS][0]->id);

        $this->assertCount(1, data_get($d, Image::SIGNATURE));
        $this->assertEquals($i_8->id, $d[Image::SIGNATURE][0]->id);
    }
}


