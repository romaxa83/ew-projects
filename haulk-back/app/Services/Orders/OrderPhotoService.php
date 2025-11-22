<?php

namespace App\Services\Orders;

use App\Models\Orders\Vehicle;
use Exception;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class OrderPhotoService
{
    public const VEHICLE_ARCHIVE_NAME = 'vehicle-photos.zip';

    public const ORDER_ARCHIVE_NAME = 'order-photos.zip';

    private OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @param string $token
     * @param int $vehicle_id
     * @return StreamedResponse
     * @throws Exception
     */
    public function vehiclePhotos(string $token, int $vehicle_id): StreamedResponse
    {
        $order = $this->orderService->getOrderByPublicToken($token);

        $vehicle = $order->vehicles->where('id', $vehicle_id)->first();

        if (!$vehicle) {
            throw new Exception(trans('Vehicle not found.'));
        }

        $headers = [
            'Content-Disposition' => "attachment; filename=\"" . self::VEHICLE_ARCHIVE_NAME . "\"",
            'Content-Type' => 'application/octet-stream',
        ];

        return new StreamedResponse(
            function () use ($vehicle) {
                $zip = new ZipStream(self::VEHICLE_ARCHIVE_NAME);

                $mediaCollection = $this->getVehicleMediaCollection($vehicle);

                $mediaCollection->each(
                    function (array $mediaInZip) use ($zip) {
                        $stream = $mediaInZip['media']->stream();

                        $zip->addFileFromStream($mediaInZip['fileNameInZip'], $stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }
                );

                $zip->finish();

                return $zip;
            }, 200, $headers
        );
    }

    private function getVehicleMediaCollection(Vehicle $vehicle, $vehicleIndex = 1): Collection
    {
        $mediaCollection = collect();

        if ($vehicle->type_id) {
            foreach ($vehicle->pickupInspection->getPhotos() as $media) {
                $mediaCollection->push(
                    [
                        'fileNameInZip' => $this->getFileNameWithSuffix(
                            'Pickup_Inspection',
                            $media->file_name,
                            $vehicle->vin ? 'VIN_' . preg_replace(
                                    '/[\W]/',
                                    '',
                                    $vehicle->vin
                                ) : 'vehicle' . $vehicleIndex,
                            $media->id
                        ),
                        'media' => $media,
                    ]
                );
            }
            foreach ($vehicle->deliveryInspection->getPhotos() as $media) {
                $mediaCollection->push(
                    [
                        'fileNameInZip' => $this->getFileNameWithSuffix(
                            'Delivery_Inspection',
                            $media->file_name,
                            $vehicle->vin ? 'VIN_' . preg_replace(
                                    '/[\W]/',
                                    '',
                                    $vehicle->vin
                                ) : 'vehicle' . $vehicleIndex,
                            $media->id
                        ),
                        'media' => $media,
                    ]
                );
            }
        }

        return $mediaCollection;
    }

    private function getFileNameWithSuffix(
        string $inspectionType,
        string $fileName,
        string $vehicleName,
        int $photoIndex
    ): string {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        return "{$inspectionType}/{$vehicleName}/photo{$photoIndex}.{$extension}";
    }

    public function orderPhotos(string $token): StreamedResponse
    {
        $order = $this->orderService->getOrderByPublicToken($token);

        $headers = [
            'Content-Disposition' => "attachment; filename=\"" . self::ORDER_ARCHIVE_NAME . "\"",
            'Content-Type' => 'application/octet-stream',
        ];

        return new StreamedResponse(
            function () use ($order) {
                $zip = new ZipStream(self::ORDER_ARCHIVE_NAME);

                $mediaCollection = collect();

                foreach ($order->vehicles as $i => $vehicle) {
                    $mediaCollection = $mediaCollection->concat(
                        $this->getVehicleMediaCollection($vehicle, $i + 1)
                    );
                }

                $mediaCollection->each(
                    function (array $mediaInZip) use ($zip) {
                        $stream = $mediaInZip['media']->stream();

                        $zip->addFileFromStream($mediaInZip['fileNameInZip'], $stream);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                    }
                );

                $zip->finish();

                return $zip;
            }, 200, $headers
        );
    }
}
