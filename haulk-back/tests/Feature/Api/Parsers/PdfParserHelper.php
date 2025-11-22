<?php


namespace Tests\Feature\Api\Parsers;


use App\Models\Locations\City;
use App\Models\Locations\State;
use App\Models\VehicleDB\VehicleMake;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

abstract class PdfParserHelper extends TestCase
{
    use DatabaseTransactions;

    protected int $pickupStateId;
    protected int $deliveryStateId;
    protected int $shipperStateId;

    protected string $pickupTimezone;
    protected string $deliveryTimezone;
    protected string $shipperTimezone;


    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsCarrierDispatcher();
    }

    abstract protected function getFolderName(): string;

    protected function sendPdfFile (string $file): TestResponse
    {
        return $this->postJson(
            route('parse.pdf_order'),
            [
                'order_pdf' => UploadedFile::fake()
                    ->createWithContent(
                        $file . '.pdf',
                        file_get_contents(base_path() . '/tests/Data/Files/Pdf/' . $this->getFolderName() . $file . '.pdf')
                    )
            ]
        );
    }

    protected function createMakes(...$makes): PdfParserHelper
    {
        foreach ($makes as $make) {
            factory(VehicleMake::class)->create(['name' => $make]);
        }

        return $this;
    }

    protected function createStates(string $pickup, string $delivery, string $shipper): PdfParserHelper
    {
        $this->pickupStateId = factory(State::class)->create(['state_short_name' => $pickup])->id;

        $this->deliveryStateId = $delivery !== $pickup ? factory(State::class)->create(['state_short_name' => $delivery])->id : $this->pickupStateId;

        $this->shipperStateId = $shipper === $delivery ? $this->deliveryStateId : ($shipper === $pickup ? $this->pickupStateId : factory(State::class)->create(['state_short_name' => $shipper])->id);

        return $this;
    }

    protected function createTimeZones(string $pickup, string $delivery, string $shipper): PdfParserHelper
    {
        $this->pickupTimezone = factory(City::class)->create(['zip' => $pickup, 'state_id' => $this->pickupStateId])->timezone;

        $this->deliveryTimezone = $delivery !== $pickup ? factory(City::class)->create(['zip' => $delivery, 'state_id' => $this->deliveryStateId])->timezone : $this->pickupTimezone;

        $this->shipperTimezone = $shipper === $delivery ? $this->deliveryTimezone : ($shipper === $pickup ? $this->pickupTimezone : factory(City::class)->create(['zip' => $shipper, 'state_id' => $this->shipperStateId])->timezone);
        return $this;
    }

}
