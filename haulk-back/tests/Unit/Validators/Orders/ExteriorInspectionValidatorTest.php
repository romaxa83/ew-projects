<?php

namespace Tests\Unit\Validators\Orders;

use App\Models\Orders\Inspection;
use App\Validators\Orders\ExteriorPickupInspectionPhotoValidator;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\DiskDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileDoesNotExist;
use Spatie\MediaLibrary\Exceptions\FileCannotBeAdded\FileIsTooBig;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class ExteriorInspectionValidatorTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    /**
     * @throws Exception
     */
    public function test_it_not_fire_validator_for_not_out_of_limit_photos()
    {
        $inspection = Inspection::factory()->create(
            [
                'has_vin_inspection' => true,
            ]
        );
        $order = $this->orderFactory(
            [
                'pickup_inspection_id' => $inspection->id,
            ]
        );
        $vehicle = $order->vehicles->first();

        $validator = new ExteriorPickupInspectionPhotoValidator($vehicle);
        $this->assertTrue($validator->passes('photo_id', 1));
    }

    /**
     * @throws DiskDoesNotExist
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws Exception
     */
    public function test_it_has_validation_error_by_photo_limit()
    {
        Config::set('orders.inspection.max_photo', 1);

        $inspection = Inspection::factory()->create(
            [
                'has_vin_inspection' => true,
            ]
        );

        $image = UploadedFile::fake()->image('image1.jpeg');
        $inspection->addPhoto(2, $image);

        $order = $this->orderFactory(
            [
                'pickup_inspection_id' => $inspection->id,
            ]
        );
        $vehicle = $order->vehicles->first();

        $validator = new ExteriorPickupInspectionPhotoValidator($vehicle);
        $this->assertFalse($validator->passes('photo_id', 1));
    }
}
