<?php

namespace App\Models\Report;

use App\Repositories\JD\ModelDescriptionRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Report\ReportClient
 *
 * @property int $id
 * @property string $customer_id
 * @property string $customer_first_name
 * @property string $customer_last_name
 * @property string $company_name
 * @property string $phone
 * @property string $comment
 * @property bool $status
 */

class ReportClient extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'reports_clients';

    public function modelDescription()
    {
        if (isset($this->pivot->model_description_id)){
            return \App::make(ModelDescriptionRepository::class)->getBy('id', $this->pivot->model_description_id);
        }

        return null;
    }

    public function modelDescriptionName()
    {
        if($md = $this->modelDescription()){
            return $md->name;
        }

        return null;
    }
}
