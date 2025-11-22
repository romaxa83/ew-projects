<?php

namespace App\Console\Commands\Syncs\BS;

use App\Console\Commands\BaseCommand;
use App\Foundations\Helpers\DbConnections;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use Symfony\Component\Console\Helper\ProgressBar;

class FetchCustomer extends BaseCommand
{
    protected $signature = 'sync:bs_customer';

    public function exec(): void
    {
        echo "[x] START... fetch customers" . PHP_EOL;

        $tags = Tag::query()->get()->pluck('id','origin_id');

        try {
            $data = DbConnections::haulk()
                ->table('bs_vehicle_owners')
                ->get()
                ->toArray()
            ;
//dd($data);
            $progressBar = new ProgressBar($this->output, count($data));
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $comments = DbConnections::haulk()
                ->table('bs_vehicle_owner_comments')
                ->get()
                ->toArray()
            ;

            $taggables = DbConnections::haulk()
                ->table('taggables')
                ->where('taggable_type', 'App\Models\BodyShop\VehicleOwners\VehicleOwner')
                ->get()
                ->toArray();

            $files = DbConnections::haulk()
                ->table('media')
                ->where('model_type', 'App\Models\BodyShop\VehicleOwners\VehicleOwner')
                ->get()
                ->toArray();

            foreach ($data as $k => $item){
                $item = std_to_array($item);
                $item['origin_id'] = $item['id'];

                if($item['phones'] == null){
                    $item['phones'] = [];
                } else {
                    $item['phones'] = json_to_array($item['phones']);
                }

                foreach ($comments as $comment){
                    $comment = std_to_array($comment);
                    if($comment['vehicle_owner_id'] == $item['id']){

                        $user = User::query()
                            ->select('id')
                            ->where('origin_id', $comment['user_id'])
                            ->first();
                        $comment['user_id'] = $user->id;

                        $item['comments'][] = $comment;
                    }
                }

                foreach ($taggables as $taggable){
                    $taggable = std_to_array($taggable);
                    if($taggable['taggable_id'] == $item['id']){
                        $item['tags'][] = $tags[$taggable['tag_id']];
                    }
                }

                unset(
                    $item['id'],
                );
                $data[$k] = $item;
            }

            foreach ($data as $k => $item){
                if(!Customer::query()->where('origin_id', $item['origin_id'])->exists()){
                    $model = new Customer();
                    $model->fill($item);
                    $model->save();

                    foreach ($item['comments'] ?? [] as $c){
                        $com = new Comment();
                        $com->model_type = Customer::MORPH_NAME;
                        $com->model_id = $model->id;
                        $com->author_id = $c['user_id'];
                        $com->text = $c['comment'];
                        $com->created_at = $c['created_at'];
                        $com->updated_at = $c['updated_at'];
                        $com->save();
                    }

                    foreach ($files as $file){
                        if($file->model_id == $item['origin_id']){
                            $props = json_to_array($file->custom_properties);
                            $conversion = $props['generated_conversions'] ?? [];

                            $media = new Media();
                            $media->model_type = Customer::MORPH_NAME;
                            $media->model_id = $model->id;
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

                    if(isset($item['tags'])){
                        $model->tags()->sync($item['tags']);
                    }

                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            echo PHP_EOL;
            echo "[x]  DONE fetch customers" . PHP_EOL;
        } catch (\Throwable $e) {
            $this->error($e->getMessage() . ' ' . $e->getTraceAsString());
        }
    }
}

