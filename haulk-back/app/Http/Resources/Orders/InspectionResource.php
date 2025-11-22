<?php

namespace App\Http\Resources\Orders;

use App\Http\Resources\Files\ImageGeoResource;
use App\Http\Resources\Files\ImageResource;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\Models\Media;

/**
 * @mixin Inspection
 */
class InspectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     *
     * @OA\Schema(
     *    schema="InspectionResource",
     *    type="object",
     *        allOf={
     *            @OA\Schema(
     *                @OA\Property(property="vin", type="string", description=""),
     *                @OA\Property(property="vin_scan", type="object", description="", allOf={
     *                              @OA\Schema(ref="#/components/schemas/Image")
     *                        }),
     *                @OA\Property(property="damage_photo", type="object", description="", allOf={
     *                              @OA\Schema(ref="#/components/schemas/Image")
     *                        }),
     *                @OA\Property(property="damage_labeled_photo", type="object", description="", allOf={
     *                              @OA\Schema(ref="#/components/schemas/Image")
     *                        }),
     *                @OA\Property(property="condition_dark", type="boolean", description=""),
     *                @OA\Property(property="condition_snow", type="boolean", description=""),
     *                @OA\Property(property="condition_rain", type="boolean", description=""),
     *                @OA\Property(property="condition_dirty_vehicle", type="boolean", description=""),
     *                @OA\Property(property="odometer", type="number", description=""),
     *                @OA\Property(property="notes", type="string", description=""),
     *                @OA\Property(property="num_keys", type="number", description=""),
     *                @OA\Property(property="num_remotes", type="number", description=""),
     *                @OA\Property(property="num_headrests", type="number", description=""),
     *                @OA\Property(property="drivable", type="boolean", description=""),
     *                @OA\Property(property="windscreen", type="boolean", description=""),
     *                @OA\Property(property="glass_all_intact", type="boolean", description=""),
     *                @OA\Property(property="title", type="boolean", description=""),
     *                @OA\Property(property="cargo_cover", type="boolean", description=""),
     *                @OA\Property(property="spare_tire", type="boolean", description=""),
     *                @OA\Property(property="radio", type="boolean", description=""),
     *                @OA\Property(property="manuals", type="boolean", description=""),
     *                @OA\Property(property="navigation_disk", type="boolean", description=""),
     *                @OA\Property(property="plugin_charger_cable", type="boolean", description=""),
     *                @OA\Property(property="headphones", type="boolean", description=""),
     *                @OA\Property(property="photos", type="array", description="", @OA\Items(
     *                    allOf={
     *                        @OA\Schema(ref="#/components/schemas/ImageGeo")
     *                    }
     *                )),
     *                @OA\Property(property="has_vin_inspection", type="boolean", description=""),
     *                @OA\Property(property="has_inspection", type="boolean", description=""),
     *            )
     *        }
     * )
     *
     */
    public function toArray($request): array
    {
        $photos = $this->getMediaByWildcard(Order::INSPECTION_PHOTO_COLLECTION_NAME)
            ->mapWithKeys(fn(Media $media) => [$media->collection_name => ImageGeoResource::make($media)]);

        return [
            'vin' => $this->vin,
            Order::VIN_SCAN_FIELD_NAME => ImageResource::make(
                $this->getFirstMedia(Order::VIN_SCAN_COLLECTION_NAME)
            ),
            Order::INSPECTION_DAMAGE_FIELD_NAME => ImageResource::make(
                $this->getFirstMedia(
                    Order::INSPECTION_DAMAGE_COLLECTION_NAME
                )
            ),
            Order::INSPECTION_DAMAGE_LABELED_COLLECTION_NAME => ImageResource::make(
                $this->getFirstMedia(
                    Order::INSPECTION_DAMAGE_LABELED_COLLECTION_NAME
                )
            ),
            'condition_dark' => $this->condition_dark,
            'condition_snow' => $this->condition_snow,
            'condition_rain' => $this->condition_rain,
            'condition_dirty_vehicle' => $this->condition_dirty_vehicle,
            'odometer' => $this->odometer,
            'notes' => $this->notes,
            'num_keys' => $this->num_keys,
            'num_remotes' => $this->num_remotes,
            'num_headrests' => $this->num_headrests,
            'drivable' => $this->drivable,
            'windscreen' => $this->windscreen,
            'glass_all_intact' => $this->glass_all_intact,
            'title' => $this->title,
            'cargo_cover' => $this->cargo_cover,
            'spare_tire' => $this->spare_tire,
            'radio' => $this->radio,
            'manuals' => $this->manuals,
            'navigation_disk' => $this->navigation_disk,
            'plugin_charger_cable' => $this->plugin_charger_cable,
            'headphones' => $this->headphones,
            'photos' => $photos->isNotEmpty() ? $photos->toArray() : (object)[],
            'has_vin_inspection' => $this->has_vin_inspection,
            'has_inspection' => (
                ($this->has_vin_inspection)
                && ($this->odometer !== null || $this->notes !== null)
            ),
        ];
    }
}
