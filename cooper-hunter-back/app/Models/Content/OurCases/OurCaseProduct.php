<?php

namespace App\Models\Content\OurCases;

use App\Models\BasePivot;

/**
 * @property int our_case_id
 * @property int product_id
 */
class OurCaseProduct extends BasePivot
{
    public const TABLE = 'our_case_product';

    protected $table = self::TABLE;
}
