<?php


namespace App\Console\Commands;


use App\Models\History\History;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AddPaymentStageFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:fix-payment-stage {--add-data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix payment stage';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle(): int
    {
        if ($this->option('add-data')) {
            return $this->addData();
        }

        $histories = History::where('model_type', Order::class)
            ->where('message', 'history.order_marked_paid')
            ->whereExists(
                fn (Builder $builder) => $builder->select(DB::raw(1))->from('orders')->whereColumn('orders.id', 'histories.model_id')
            )
            ->get();

        foreach ($histories as $history) {
            if (
                History::where('model_id', $history->model_id)
                    ->where('model_type', Order::class)
                    ->where('performed_at', '>', $history->performed_at)
                    ->where('message', 'history.order_marked_unpaid')
                    ->exists()
            ) {
                continue;
            }

            $payment = Payment::where('order_id', $history->model_id)->first();

            if ($payment->broker_payment_amount) {
                PaymentStage::firstOrCreate(
                    [
                        'amount' => $payment->broker_payment_amount,
                        'payer' => Payment::PAYER_BROKER,
                        'order_id' => $payment->order_id,
                    ],
                    [
                        'method_id' => $payment->broker_payment_method_id,
                        'payment_date' => $history->performed_at->getTimestamp(),
                    ]
                );
            }

            if ($payment->customer_payment_amount) {
                PaymentStage::firstOrCreate(
                    [
                        'amount' => $payment->customer_payment_amount,
                        'payer' => Payment::PAYER_CUSTOMER,
                        'order_id' => $payment->order_id
                    ],
                    [
                        'method_id' => $payment->customer_payment_method_id,
                        'payment_date' => $history->performed_at->getTimestamp(),
                    ]
                );
            }

            $this->info("Order: " . $history->model_id);
        }
        return self::SUCCESS;
    }

    private function addData(): int
    {
        $paymentData = DB::connection('pgsql_backup')
            ->table('payments')
            ->select('order_id', 'uship_number', 'receipt_number', 'invoice_notes')
            ->whereNotNull('uship_number')
            ->orWhereNotNull('receipt_number')
            ->orWhereNotNull('invoice_notes')
            ->get();

        for ($i = 0, $max = $paymentData->count(); $i < $max; $i++) {
            PaymentStage::where('order_id', $paymentData[$i]->order_id)
                ->update(
                    [
                        'uship_number' => $paymentData[$i]->uship_number,
                        'reference_number' => $paymentData[$i]->receipt_number,
                        'notes' => $paymentData[$i]->invoice_notes
                    ]
                );

            $this->info('Order: ' . $paymentData[$i]->order_id . '. ' . $i . '/' . $max);
        }

        return self::SUCCESS;
    }
}
