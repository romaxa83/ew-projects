<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report\Location
 *
 * @property int $id
 * @property string $report_id
 * @property string $name
 * @property string $url
 */

class Video extends Model
{
    use HasFactory;

    protected $table = 'reports_videos';

    // relation
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
