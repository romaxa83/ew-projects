<?php

namespace Api\Reports;

use App\Broadcasting\Events\DriverTripReport\DriverTripReportCreateBroadcast;
use App\Broadcasting\Events\DriverTripReport\DriverTripReportDeleteBroadcast;
use App\Broadcasting\Events\DriverTripReport\DriverTripReportUpdateBroadcast;
use App\Models\Reports\DriverTripReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class DriverTripReportTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;


    public function test_list_driver_trip_report()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        DriverTripReport::factory()->times(10)->create(['driver_id' => $user->id]);

        $response = $this->getJson(route('driver-trip-report.index'));

        $response->assertOk()->assertJsonCount(10, 'data')->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'driver_id',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            ]
        );

        $user = $this->driverFactory();
        DriverTripReport::factory()->times(4)->create(['driver_id' => $user->id]);

        $response = $this->getJson(route('driver-trip-report.index', ['driver_id' => $user->id]));
        $response->assertOk()->assertJsonCount(4, 'data')->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'driver_id',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            ]
        );

        $date = Carbon::now()->subYears(9);

        DriverTripReport::factory()->times(1)->create(['driver_id' => $user->id, 'report_date' => $date]);

        $response = $this->getJson(route('driver-trip-report.index', ['report_date' => $date->format(config('formats.date'))]));
        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'driver_id',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            ]
        );


        DriverTripReport::factory()->times(4)->create(
            [
                'driver_id' => $user->id,
                'date_from' => Carbon::now()->subDays(9),
                'date_to' => Carbon::now()->subDays(2),

            ]
        );

        $response = $this->getJson(
            route(
                'driver-trip-report.index',
                [
                    'dates_range' => Carbon::now()->subDays(10)->format(config('formats.date')) . ' - ' . Carbon::now()->subDays(1)->format(config('formats.date')),
                ]
            )
        );
        $response->assertOk()->assertJsonCount(4, 'data')->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'driver_id',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            ]
        );


        DriverTripReport::factory()->times(7)->create(
            [
                'driver_id' => $user->id,
                'date_from' => Carbon::now()->subMonths(1)->subDays(9),
                'date_to' => Carbon::now()->subMonths(1)->subDays(2),

            ]
        );

        $response = $this->getJson(
            route(
                'driver-trip-report.index',
                [
                    'dates_range' => Carbon::now()->subMonths(1)->subDays(30)->format(config('formats.date')) . ' - ' . Carbon::now()->subMonths(1)->subDays(8)->format(config('formats.date')),
                ]
            )
        );
        $response->assertOk()->assertJsonCount(7, 'data')->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'driver_id',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            ]
        );


        DriverTripReport::factory()->times(2)->create(
            [
                'driver_id' => $user->id,
                'date_from' => Carbon::now()->subMonths(2)->subDays(10),
                'date_to' => Carbon::now()->subMonths(2)->subDays(3),

            ]
        );

        $response = $this->getJson(
            route(
                'driver-trip-report.index',
                [
                    'dates_range' => Carbon::now()->subMonths(2)->subDays(3)->format(config('formats.date')) . ' - ' . Carbon::now()->subMonths(2)->addDays(8)->format(config('formats.date')),
                ]
            )
        );
        $response->assertOk()->assertJsonCount(2, 'data')->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'driver_id',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            ]
        );
    }

    public function test_destroy_driver_trip_report()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        $tripReport = DriverTripReport::factory()->create(['driver_id' => $user->id]);

        Event::fake();
        // destroy
        $this->deleteJson(
            route(
                'driver-trip-report.destroy',
                $tripReport->id
            )
        )
            ->assertNoContent();

        Event::assertDispatched(DriverTripReportDeleteBroadcast::class);

        // check deleted
        $this->assertDatabaseMissing(
            DriverTripReport::TABLE_NAME,
            [
                'id' => $tripReport->id,
            ]
        );
    }


    public function test_store_driver_trip_report()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        Event::fake();

        $response = $this->postJson(
            route('driver-trip-report.store'),
            [
                'driver_id' => $user->id,
                'report_date' => Carbon::now()->format(config('formats.date')),
                DriverTripReport::DRIVER_FILE_FIELD_NAME => UploadedFile::fake()->create('image.pdf'),
                'date_from' => Carbon::now()->format(config('formats.date')),
                'date_to' => Carbon::now()->format(config('formats.date')),
            ]
        );

        Event::assertDispatched(DriverTripReportCreateBroadcast::class);

        $report = $response->json('data');
        $this->assertDatabaseHas(DriverTripReport::TABLE_NAME, ['id' => $report['id']]);
    }


    public function test_delete_file_driver_trip_report()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        Event::fake([
            DriverTripReportCreateBroadcast::class,
            DriverTripReportUpdateBroadcast::class
        ]);

        $response = $this->postJson(
            route('driver-trip-report.store'),
            [
                'driver_id' => $user->id,
                'report_date' => Carbon::now()->format(config('formats.date')),
                DriverTripReport::DRIVER_FILE_FIELD_NAME => UploadedFile::fake()->create('image.pdf'),
                'date_from' => Carbon::now()->format(config('formats.date')),
                'date_to' => Carbon::now()->format(config('formats.date')),
            ]
        )->assertCreated()
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'driver_id',
                        'driver',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file' => ['id'],
                    ]
                ]
            );

        Event::assertDispatched(DriverTripReportCreateBroadcast::class);

        $report = $response->json('data');
        $this->assertDatabaseHas(DriverTripReport::TABLE_NAME, ['id' => $report['id']]);

        // destroy file
        $this->deleteJson(
            route(
                'driver-trip-report.delete-file',
                [
                    'driver_trip_report' => $report['id'],
                    'id' => $report['file']['id']
                ]
            )
        )->assertNoContent();

        Event::assertDispatched(DriverTripReportUpdateBroadcast::class);

        $this->getJson(route('driver-trip-report.show', $report['id']))
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        'id' => $report['id'],
                        'file' => [],
                    ]
                ]
            );
    }


    public function test_update_driver_trip_report()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        $tripReport = DriverTripReport::factory()->create(['driver_id' => $user->id]);

        Event::fake();

        $response = $this->postJson(
            route('driver-trip-report.update', $tripReport->id),
            [
                'driver_id' => $user->id,
                'report_date' => now()->format(config('formats.date')),
                'date_from' => now()->format(config('formats.date')),
                DriverTripReport::DRIVER_FILE_FIELD_NAME => UploadedFile::fake()->create('image.pdf'),
                'date_to' => now()->format(config('formats.date')),
            ]
        )->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'driver_id',
                        'driver',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file' => ['id'],
                    ]
                ]
            );

        Event::assertDispatched(DriverTripReportUpdateBroadcast::class);

        $report = $response->json('data');
        $this->assertDatabaseHas(DriverTripReport::TABLE_NAME, ['id' => $report['id']]);
    }


    public function test_show_driver_trip_report()
    {
        $this->loginAsCarrierSuperAdmin();

        $user = $this->driverFactory();

        $tripReport = DriverTripReport::factory()->create(['driver_id' => $user->id]);

        $this->getJson(route('driver-trip-report.show', $tripReport->id))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'driver_id',
                        'driver',
                        'report_date',
                        'date_from',
                        'date_to',
                        'file',
                    ]
                ]
            );
    }
}
