<?php

namespace App\Resources\Custom;

use App\Models\Report\Feature\FeatureValue;
use App\Models\Report\Feature\FeatureValueTranslates;

/**
 * @OA\Schema(type="object", title="CustomFeatureValueForSelectResource",
 *     @OA\Property(property="id", type="string", example="озимая пшеница",
 *           description="ключ - id значения, значение - название по той локали, которая установленна в системе"
 *     )
 * )
 */

class CustomFeatureValueForSelectResource
{}


