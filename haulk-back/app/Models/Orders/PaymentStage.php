<?php

namespace App\Models\Orders;

use App\Collections\Models\Orders\PaymentStageCollection;
use App\Models\DiffableInterface;
use App\Traits\Diffable;
use Carbon\Carbon;
use Database\Factories\Orders\PaymentStageFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property float $amount
 * @property int $payment_date
 * @property string $payer
 * @property int $method_id
 * @property string|null $uship_number
 * @property string|null $reference_number
 * @property string|null $notes
 *
 * @mixin Eloquent
 *
 * @method static PaymentStageFactory factory(...$parameters)
 */
class PaymentStage extends Model implements DiffableInterface
{
    use HasFactory;
    use Diffable;

    public const TABLE_NAME = 'payment_stages';

    public $timestamps = false;

    protected $fillable = [
        'amount',
        'order_id',
        'payment_date',
        'payer',
        'method_id',
        'uship_number',
        'reference_number',
        'notes',
    ];

//    protected $dates = [
//        'payment_date'
//    ];


    public function newCollection(array $models = []): PaymentStageCollection
    {
        return PaymentStageCollection::make($models);
    }

    public function renderForPdf(): string
    {
        $paymentMethod = Payment::ALL_METHODS[$this->method_id];
        $payer = ucfirst($this->payer);

        $msg = "{$payer} - $".$this->amount." via {$paymentMethod}";

        if($this->payment_date){
            $date  = Carbon::createFromTimestamp($this->payment_date)->format('m/d/Y');

            $msg .= ' at ' . $date . ',';
        }
        if($this->reference_number){
            $msg .= ' Reference number: ' . $this->reference_number. ',';
        }
        if($this->notes){
            $msg .= ' Note: ' . $this->notes. ',';
        }
        if($this->uship_number){
            $msg .= ' uShip number: ' . $this->uship_number. ',';
        }

        return $msg;
    }
}
