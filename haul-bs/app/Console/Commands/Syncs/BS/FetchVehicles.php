<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Enums\Vehicles\VehicleType;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\History\Services\VehicleHistoryService;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Foundations\Modules\Permission\Roles\MechanicRole;
use App\Foundations\Modules\Permission\Roles\SuperAdminRole;
use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchVehicles extends BaseCommand
{
    protected $signature = 'sync:bs_vehicle';

    public function exec(): void
    {
        $this->truck();
        $this->trailers();
    }

    public function truck()
    {
        echo "[x] START... fetch trucks" . PHP_EOL;

        $tags = Tag::query()->get()->pluck('id','origin_id');
        $users = User::query()->get()->pluck('id','origin_id');
        $customers = Customer::query()->get()->pluck('id','origin_id');

        $bsRoles = [
            'BodyShopMechanic' => MechanicRole::NAME,
            'BodyShopAdmin'  => AdminRole::NAME,
            'BodyShopSuperAdmin'  => SuperAdminRole::NAME,
        ];
        $messages = [
            'history.vehicle_created' => VehicleHistoryService::HISTORY_MESSAGE_CREATED,
            'history.vehicle_updated' => VehicleHistoryService::HISTORY_MESSAGE_UPDATED,
            'history.vehicle_file_deleted' => VehicleHistoryService::HISTORY_MESSAGE_FILE_DELETED,
            'history.vehicle_comment_created' => VehicleHistoryService::HISTORY_MESSAGE_COMMENT_CREATED,
            'history.vehicle_comment_deleted' => VehicleHistoryService::HISTORY_MESSAGE_COMMENT_DELETED,
        ];

        try {
            $companies = DbConnections::haulk()
                ->table('companies')
                ->select(['id', 'name'])
                ->where('use_in_body_shop', true)
                ->get()
                ->pluck('name', 'id')
                ->toArray()
            ;

            foreach ($companies as $id => $name){
                if(!Company::query()->where('id', $id)->exists()){
                    $c = new Company();
                    $c->id = $id;
                    $c->name = $name;
                    $c->save();
                }
            }

            $companiesIds = array_flip($companies);

            $trucks = DbConnections::haulk()
                ->table('trucks')
                ->whereNull(['carrier_id', 'broker_id'])
                ->orWhere(
                    function ($q) use ($companiesIds) {
                        $q->whereIn('carrier_id', $companiesIds)
                            ->orWhereIn('broker_id', $companiesIds);
                    }
                )
                ->get()
                ->toArray();

            $progressBar = new ProgressBar($this->output, count($trucks));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $comments = DbConnections::haulk()
                ->table('truck_comments')
                ->get()
                ->toArray();

            $histories =  DbConnections::haulk()
                ->table('histories')
                ->where('model_type', 'App\Models\Vehicles\Truck')
                ->get()
                ->toArray();


            $taggables = DbConnections::haulk()
                ->table('taggables')
                ->where('taggable_type', 'App\Models\Vehicles\Truck')
                ->get()
                ->toArray();

            $files = DbConnections::haulk()
                ->table('media')
                ->where('model_type', 'App\Models\Vehicles\Truck')
                ->get()
                ->toArray();

            foreach ($trucks as $k => $item){

                if(!Truck::query()->where('origin_id', $item->id)->exists()){
                    $customer = null;
                    $fetchData = true;

                    if($item->customer_id == null) {

                        $fetchData = false;
                        $owner = DbConnections::haulk()
                            ->table('users')
                            ->where('id', $item->owner_id)
                            ->first();

                        if(
                            $m = Customer::query()
                                ->where('origin_id', $item->owner_id)
                                ->where('from_haulk', true)
                                ->first()
                        ){
                            $customer = $m;
                        } else {
                            $customer = new Customer();
                            $customer->origin_id = $owner->id;
                            $customer->first_name = $owner->first_name;
                            $customer->last_name = $owner->last_name;
                            $customer->email = is_numeric($owner->email)
                                ? $owner->email . '.wrong@gmail.com'
                                : $owner->email;
                            $customer->phone = $owner->phone;
                            $customer->phone_extension = $owner->phone_extension;
                            $customer->phones = $owner->phones != null
                                ? json_to_array($owner->phones)
                                : [];
                            $customer->from_haulk = true;

                            $customer->save();
                        }
                    }

                    $t = new Truck();
                    $t->customer_id = $customer
                        ? $customer->id
                        : $customers[$item->customer_id];
                    $t->origin_id = $item->id;
                    $t->vin = $item->vin;
                    $t->unit_number = $item->unit_number;
                    $t->make = $item->make;
                    $t->model = $item->model;
                    $t->year = $item->year;
                    $t->color = $item->color;
                    $t->gvwr = $item->gvwr ?? null;
                    $t->type = $item->type;
                    $t->license_plate = $item->license_plate;
                    $t->temporary_plate = $item->temporary_plate;
                    $t->notes = $item->notes;
                    $t->created_at = $item->created_at;
                    $t->updated_at = $item->updated_at;

                    if($item->carrier_id != null){
                        $t->company_id = $item->carrier_id;
                    } elseif ($item->broker_id != null) {
                        $t->company_id = $item->broker_id;
                    }
                    $t->save();

                    if($fetchData){
                        foreach ($comments as $comment){
                            if($comment->truck_id == $item->id){
                                $c = new Comment();
                                $c->model_type = Truck::MORPH_NAME;
                                $c->model_id = $t->id;
                                $c->author_id = $users[$comment->user_id];
                                $c->text = $comment->comment;
                                $c->created_at = $comment->created_at;
                                $c->updated_at = $comment->updated_at;
                                $c->timezone = $comment->timezone;
                                $c->save();
                            }
                        }
                    }

                    $tagsForModel = [];
                    foreach ($taggables as $taggable){
                        if($taggable->taggable_id == $item->id){
                            $tagsForModel[] = $tags[$taggable->tag_id];
                        }
                    }
                    $t->tags()->sync($tagsForModel);

                    foreach ($files ?? [] as $file){
                        if($file->model_id == $item->id){
                            $props = json_to_array($file->custom_properties);
                            $conversion = $props['generated_conversions'] ?? [];

                            $media = new Media();
                            $media->model_type = Truck::MORPH_NAME;
                            $media->model_id = $t->id;
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

                    if($fetchData){
                        foreach ($histories as $history){
                            if($history->model_id == $item->id){
                                $h = new History();
                                $h->model_type = Truck::MORPH_NAME;
                                $h->model_id = $t->id;
                                $h->type = $history->type == 1
                                    ? HistoryType::CHANGES
                                    : HistoryType::ACTIVITY
                                ;

                                if(array_key_exists($history->user_id, $users->toArray())){
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
                    }
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch trucks" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage() . ' ' . $e->getTraceAsString());
        }
    }

    public function trailers()
    {
        echo "[x] START... fetch trailers" . PHP_EOL;

        $tags = Tag::query()->get()->pluck('id','origin_id');
        $users = User::query()->get()->pluck('id','origin_id');
        $customers = Customer::query()->get()->pluck('id','origin_id');

        $bsRoles = [
            'BodyShopMechanic' => MechanicRole::NAME,
            'BodyShopAdmin'  => AdminRole::NAME,
            'BodyShopSuperAdmin'  => SuperAdminRole::NAME,
        ];
        $messages = [
            'history.vehicle_created' => VehicleHistoryService::HISTORY_MESSAGE_CREATED,
            'history.vehicle_updated' => VehicleHistoryService::HISTORY_MESSAGE_UPDATED,
            'history.vehicle_file_deleted' => VehicleHistoryService::HISTORY_MESSAGE_FILE_DELETED,
            'history.vehicle_comment_created' => VehicleHistoryService::HISTORY_MESSAGE_COMMENT_CREATED,
            'history.vehicle_comment_deleted' => VehicleHistoryService::HISTORY_MESSAGE_COMMENT_DELETED,
        ];

        try {
            $companies = DbConnections::haulk()
                ->table('companies')
                ->select(['id', 'name'])
                ->where('use_in_body_shop', true)
                ->get()
                ->pluck('name', 'id')
                ->toArray()
            ;

            foreach ($companies as $id => $name){
                if(!Company::query()->where('id', $id)->exists()){
                    $c = new Company();
                    $c->id = $id;
                    $c->name = $name;
                    $c->save();
                }
            }

            $companiesIds = array_flip($companies);

            $trailers = DbConnections::haulk()
                ->table('trailers')
                ->whereNull(['carrier_id', 'broker_id'])
                ->orWhere(
                    function ($q) use ($companiesIds) {
                        $q->whereIn('carrier_id', $companiesIds)
                            ->orWhereIn('broker_id', $companiesIds);
                    }
                )
                ->get()
                ->toArray();

            $progressBar = new ProgressBar($this->output, count($trailers));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $comments = DbConnections::haulk()
                ->table('trailer_comments')
                ->get()
                ->toArray()
            ;

            $histories =  DbConnections::haulk()
                ->table('histories')
                ->where('model_type', 'App\Models\Vehicles\Trailer')
                ->get()
                ->toArray();


            $taggables = DbConnections::haulk()
                ->table('taggables')
                ->where('taggable_type', 'App\Models\Vehicles\Trailer')
                ->get()
                ->toArray();

            $files = DbConnections::haulk()
                ->table('media')
                ->where('model_type', 'App\Models\Vehicles\Trailer')
                ->get()
                ->toArray();

            foreach ($trailers as $k => $item){

                if(!Trailer::query()->where('origin_id', $item->id)->exists()){
                    $customer = null;
                    $fetchData = true;

                    if($item->customer_id == null) {
                        $fetchData = false;
                        $owner = DbConnections::haulk()
                            ->table('users')
                            ->where('id', $item->owner_id)
                            ->first();

                        if(
                            $m = Customer::query()
                                ->where('origin_id', $item->owner_id)
                                ->where('from_haulk', true)
                                ->first()
                        ){
                            $customer = $m;
                        } else {
                            $customer = new Customer();
                            $customer->origin_id = $owner->id;
                            $customer->first_name = $owner->first_name;
                            $customer->last_name = $owner->last_name;
                            $customer->email = is_numeric($owner->email)
                                ? $owner->email . '.wrong@gmail.com'
                                : $owner->email;
                            $customer->phone = $owner->phone;
                            $customer->phone_extension = $owner->phone_extension;
                            $customer->phones = $owner->phones != null
                                ? json_to_array($owner->phones)
                                : [];
                            $customer->from_haulk = true;

                            $customer->save();
                        }
                    }

                    //if(!isset($customer)) continue;

                    $t = new Trailer();
                    $t->customer_id = isset($customer)
                        ? $customer->id
                        : $customers[$item->customer_id];
                    $t->origin_id = $item->id;
                    $t->vin = $item->vin;
                    $t->unit_number = $item->unit_number;
                    $t->make = $item->make;
                    $t->model = $item->model;
                    $t->year = $item->year;
                    $t->color = $item->color;
                    $t->gvwr = $item->gvwr ?? null;
                    $t->type = VehicleType::VEHICLE_TYPE_OTHER;
                    $t->license_plate = $item->license_plate;
                    $t->temporary_plate = $item->temporary_plate;
                    $t->notes = $item->notes;
                    $t->created_at = $item->created_at;
                    $t->updated_at = $item->updated_at;

                    if($item->carrier_id != null){
                        $t->company_id = $item->carrier_id;
                    } elseif ($item->broker_id != null) {
                        $t->company_id = $item->broker_id;
                    }
                    $t->save();

                    if($fetchData){
                        foreach ($comments as $comment){
                            if($comment->trailer_id == $item->id){
                                $c = new Comment();
                                $c->model_type = Trailer::MORPH_NAME;
                                $c->model_id = $t->id;
                                $c->author_id = $users[$comment->user_id];
                                $c->text = $comment->comment;
                                $c->created_at = $comment->created_at;
                                $c->updated_at = $comment->updated_at;
                                $c->timezone = $comment->timezone;
                                $c->save();
                            }
                        }
                    }

                    $tagsForModel = [];
                    foreach ($taggables as $taggable){
                        if($taggable->taggable_id == $item->id){
                            $tagsForModel[] = $tags[$taggable->tag_id];
                        }
                    }
                    $t->tags()->sync($tagsForModel);

                    foreach ($files ?? [] as $file){
                        if($file->model_id == $item->id){

                            $props = json_to_array($file->custom_properties);
                            $conversion = $props['generated_conversions'] ?? [];

                            $media = new Media();
                            $media->model_type = Trailer::MORPH_NAME;
                            $media->model_id = $t->id;
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

                    if($fetchData){
                        foreach ($histories as $history){
                            if($history->model_id == $item->id){
                                $h = new History();
                                $h->model_type = Trailer::MORPH_NAME;
                                $h->model_id = $t->id;
                                $h->type = $history->type == 1
                                    ? HistoryType::CHANGES
                                    : HistoryType::ACTIVITY
                                ;

                                if(array_key_exists($history->user_id, $users->toArray())){
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
                    }

                    $progressBar->advance();

                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch trailers" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            $this->error($e->getLine());
        }
    }
}
