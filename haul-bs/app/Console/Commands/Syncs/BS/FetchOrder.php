<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Enums\Orders\OrderType;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Services\OrderBSHistoryService;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Foundations\Modules\Permission\Roles\MechanicRole;
use App\Foundations\Modules\Permission\Roles\SuperAdminRole;
use App\Models\Inventories\Inventory;
use App\Models\Inventories\Transaction;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use App\Models\Orders\BS\TypeOfWork;
use App\Models\Orders\BS\TypeOfWorkInventory;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchOrder extends BaseCommand
{
    protected $signature = 'sync:bs_order';

    public function exec(): void
    {
        $this->fetchOrder();
    }

    private function fetchOrder(): void
    {
        echo "[x] START... fetch order" . PHP_EOL;

        $users = User::query()->withTrashed()->get()->whereNotNull('origin_id')->pluck('id','origin_id');
        $trucks = Truck::query()->withTrashed()->get()->whereNotNull('origin_id')->pluck('id','origin_id');
        $trailers = Trailer::query()->withTrashed()->get()->whereNotNull('origin_id')->pluck('id','origin_id');
        $inventories = Inventory::query()->withTrashed()->get()->whereNotNull('origin_id')->pluck('id','origin_id');

        $bsRoles = [
            'BodyShopMechanic' => MechanicRole::NAME,
            'BodyShopAdmin'  => AdminRole::NAME,
            'BodyShopSuperAdmin'  => SuperAdminRole::NAME,
        ];
        $messages = [
            'history.bs.order_created' => OrderBSHistoryService::HISTORY_MESSAGE_CREATED,
            'history.bs.order_changed' => OrderBSHistoryService::HISTORY_MESSAGE_UPDATED,
            'history.bs.order_delete_attachment' => OrderBSHistoryService::HISTORY_MESSAGE_DELETED_FILE,
            'history.bs.attached_document' => OrderBSHistoryService::HISTORY_MESSAGE_UPLOAD_FILE,
            'history.bs.status_changed' => OrderBSHistoryService::HISTORY_MESSAGE_STATUS_CHANGED,
            'history.bs.order_reassigned_mechanic' => OrderBSHistoryService::HISTORY_MESSAGE_REASSIGNED_MECHANIC,
            'history.bs.order_deleted' => OrderBSHistoryService::HISTORY_MESSAGE_DELETED,
            'history.bs.order_restored' => OrderBSHistoryService::HISTORY_MESSAGE_ORDER_RESTORED,
            'history.bs.order_send_docs' => OrderBSHistoryService::HISTORY_MESSAGE_ORDER_SEND_DOCS,
            'history.bs.order_created_payment' => OrderBSHistoryService::HISTORY_MESSAGE_ORDER_CREATED_PAYMENT,
            'history.bs.order_deleted_payment' => OrderBSHistoryService::HISTORY_MESSAGE_ORDER_DELETED_PAYMENT,
            'history.bs.store_order_comment' => OrderBSHistoryService::HISTORY_MESSAGE_COMMENT_CREATED,
            'history.bs.delete_order_comment' => OrderBSHistoryService::HISTORY_MESSAGE_COMMENT_DELETED,
        ];

        try {
            $orders = DbConnections::haulk()
                ->table('bs_orders')
                ->get()
                ->toArray();

            $progressBar = new ProgressBar($this->output, count($orders));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $works = DbConnections::haulk()
                ->table('bs_order_types_of_work')
                ->get()
                ->toArray();

            $work_inventories = DbConnections::haulk()
                ->table('bs_order_type_of_work_inventories')
                ->get()
                ->toArray();

            $comments = DbConnections::haulk()
                ->table('bs_order_comments')
                ->get()
                ->toArray();

            $histories = DbConnections::haulk()
                ->table('histories')
                ->where('model_type', 'App\Models\BodyShop\Orders\Order')
                ->get()
                ->toArray();

            $transactions = DbConnections::haulk()
                ->table('bs_inventory_transactions')
                ->whereNotNull('order_id')
                ->get()
                ->toArray();

            $files = DbConnections::haulk()
                ->table('media')
                ->where('model_type', 'App\Models\BodyShop\Orders\Order')
                ->get()
                ->toArray();

            $payments = DbConnections::haulk()
                ->table('bs_order_payments')
                ->get()
                ->toArray();

            foreach ($orders as $k => $item){
                if(!Order::query()->where('origin_id', $item->id)->exists()){
                    if (!isset($users[$item->mechanic_id])) {
                        continue;
                    }
                    make_transaction(function() use ($item, $users, $trucks, $trailers, $inventories, $works, $work_inventories, $comments, $payments, $transactions, $files, $histories, $bsRoles, $messages) {

                        $o = new Order();
                        $o->origin_id = $item->id;
                        $o->discount = $item->discount;
                        $o->tax_inventory = $item->tax_inventory;
                        $o->tax_labor = $item->tax_labor;
                        $o->implementation_date = $item->implementation_date;
                        $o->notes = $item->notes;
                        $o->status = $item->status;
                        $o->order_number = $item->order_number;
                        $o->due_date = $item->due_date;
                        $o->created_at = $item->created_at;
                        $o->updated_at = $item->updated_at;
                        $o->status_changed_at = $item->status_changed_at;
                        $o->status_before_deleting = $item->status_before_deleting;
                        $o->deleted_at = $item->deleted_at;
                        $o->is_billed = $item->is_billed;
                        $o->billed_at = $item->billed_at;
                        $o->is_paid = $item->is_paid;
                        $o->total_amount = $item->total_amount;
                        $o->paid_amount = $item->paid_amount;
                        $o->debt_amount = $item->debt_amount;
                        $o->mechanic_id = $users[$item->mechanic_id];

                        if ($item->truck_id) {
                            if (!isset($trucks[$item->truck_id])) {
                                return false;
                            }

                            $o->vehicle_type = Truck::MORPH_NAME;
                            $o->vehicle_id = $trucks[$item->truck_id];
                        } elseif ($item->trailer_id) {
                            if (!isset($trailers[$item->trailer_id])) {
                                return false;
                            }

                            $o->vehicle_type = Trailer::MORPH_NAME;
                            $o->vehicle_id = $trailers[$item->trailer_id];
                        }

                        $o->save();

                        foreach ($works as $work) {
                            if ($work->order_id == $item->id) {
                                $w = new TypeOfWork();
                                $w->order_id = $o->id;
                                $w->name = $work->name;
                                $w->duration = $work->duration;
                                $w->hourly_rate = $work->hourly_rate;
                                $w->created_at = $work->created_at;
                                $w->updated_at = $work->updated_at;
                                $w->save();

                                foreach ($work_inventories as $work_inventory) {
                                    if ($work_inventory->type_of_work_id == $work->id) {
                                        $wi = new TypeOfWorkInventory();
                                        $wi->type_of_work_id = $w->id;
                                        $wi->inventory_id = $inventories[$work_inventory->inventory_id];
                                        $wi->quantity = $work_inventory->quantity;
                                        $wi->price = $work_inventory->price;
                                        $wi->created_at = $work_inventory->created_at;
                                        $wi->updated_at = $work_inventory->updated_at;
                                        $wi->save();
                                    }
                                }
                            }
                        }

                        foreach ($comments as $comment) {
                            if ($comment->order_id == $item->id) {
                                $c = new Comment();
                                $c->model_type = Order::MORPH_NAME;
                                $c->model_id = $o->id;
                                $c->author_id = $users[$comment->user_id];
                                $c->text = $comment->comment;
                                $c->created_at = $comment->created_at;
                                $c->updated_at = $comment->updated_at;
                                $c->save();
                            }
                        }

                        foreach ($payments as $payment) {
                            if ($payment->order_id == $item->id) {
                                $p = new Payment();
                                $p->order_id = $o->id;
                                $p->amount = $payment->amount;
                                $p->payment_date = $payment->payment_date;
                                $p->payment_method = $payment->payment_method;
                                $p->notes = $payment->notes;
                                $p->reference_number = $payment->reference_number;
                                $p->save();
                            }
                        }

                        foreach ($transactions as $transaction) {
                            if ($transaction->order_id == $item->id) {
                                $t = new Transaction();
                                $t->inventory_id = $inventories[$transaction->inventory_id];
                                $t->order_id = $o->id;
                                $t->order_type = OrderType::BS();
                                $t->transaction_date = $transaction->transaction_date;
                                $t->quantity = $transaction->quantity;
                                $t->price = $transaction->price;
                                $t->invoice_number = $transaction->invoice_number;
                                $t->describe = $transaction->describe;
                                $t->operation_type = $transaction->operation_type;
                                $t->is_reserve = $transaction->is_reserve;
                                $t->created_at = $transaction->created_at;
                                $t->updated_at = $transaction->updated_at;
                                $t->discount = $transaction->discount;
                                $t->tax = $transaction->tax;
                                $t->payment_date = $transaction->payment_date;
                                $t->first_name = $transaction->first_name;
                                $t->last_name = $transaction->last_name;
                                $t->phone = $transaction->phone;
                                $t->email = $transaction->email;
                                $t->company_name = $transaction->company_name;
                                $t->payment_method = $transaction->payment_method;
                                $t->origin_id = $transaction->id;
                                $t->save();
                            }
                        }

                        foreach ($files ?? [] as $file) {
                            if ($file->model_id == $item->id) {
                                $props = json_to_array($file->custom_properties);
                                $conversion = $props['generated_conversions'] ?? [];

                                $media = new Media();
                                $media->model_type = Order::MORPH_NAME;
                                $media->model_id = $o->id;
                                $media->collection_name = $file->collection_name;
                                $media->name = $file->name;
                                $media->file_name = $file->file_name;
                                $media->mime_type = $file->mime_type;
                                $media->disk = $file->disk;
                                $media->conversions_disk = $file->disk;
                                $media->size = $file->size;
                                $media->manipulations = json_to_array($file->manipulations);
                                $media->custom_properties = json_to_array($file->custom_properties);
                                $media->responsive_images = json_to_array($file->responsive_images);
                                $media->generated_conversions = $conversion;
                                $media->order_column = $file->order_column;
                                $media->created_at = $file->created_at;
                                $media->updated_at = $file->updated_at;
                                $media->origin_id = $file->id;
                                $media->save();
                            }
                        }

                        foreach ($histories as $history) {
                            if ($history->model_id == $item->id) {
                                $h = new History();
                                $h->model_type = Order::MORPH_NAME;
                                $h->model_id = $o->id;
                                $h->type = $history->type == 1
                                    ? HistoryType::CHANGES
                                    : HistoryType::ACTIVITY;

                                if (array_key_exists($history->user_id, $users->toArray())) {
                                    $h->user_id = $users->toArray()[$history->user_id];
                                    $h->user_role = $bsRoles[$history->user_role];
                                }

                                $h->msg = $messages[$history->message];
                                $h->msg_attr = json_to_array($history->meta);
                                $h->details = json_to_array($history->histories);
                                $h->performed_at = $history->performed_at;
                                $h->performed_timezone = $history->performed_timezone;

                                $h->save();
                            }
                        }
                    });

                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch fetch order" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            logger_info($e);
        }
    }
}
