<?php

namespace WezomCms\Catalog\Services;

use Exception;
use Log;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\ProductTranslation;
use DB;

class ProductService
{
    public function createFromImport(array $data): Product
    {
        DB::beginTransaction();
        try {
            $model = new Product();
            $model->provider_id = $data['provider_id'];
            $model->moderator_id = $data['moderator_id'];
            $model->group_key = $data['group_key'];
            $model->cost = $data['cost'];
            $model->cost_discount = $data['costDiscount'];
            $model->amount_one_user = $data['amountOneUser'] ?? 0;
            $model->amount = $data['amount'] ?? 0;
            $model->weight = $data['weight'] ?? 0;
            $model->sort = $data['sort'] ?? 0;
            $model->published_at = $data['publishedAt'] ?? null;
            $model->expires_at = $data['expiresAt'] ?? null;
            $model->category_id = $data['category_id'] ?? null;
            $model->brand_id = $data['brand_id'] ?? null;
            $model->dimensions = $data['dimensions'] ?? null;
            $model->published = false;
            $model->save();

            if(isset($data['collection_id'])){
                $model->collections()->sync($data['collection_id']);
            }

            foreach ($data['specifications'] ?? [] as $item) {
                DB::table('product_specifications')->insert([
                    "product_id" => $model->id,
                    "spec_id" => $item['spec_id'],
                    "spec_value_id" => $item['value_id'],
                ]);
            }

            foreach ($data['labels'] ?? [] as $id) {
                DB::table('product_label_relations')->insert([
                    "product_id" => $model->id,
                    "label_id" => $id,
                ]);
            }

            foreach ($data['translations'] ?? [] as $locale => $item) {
                $t = new ProductTranslation();
                $t->product_id = $model->id;
                $t->locale = $locale;
                $t->name = $item['name'];
                $t->description = $item['description'];
                $t->feature_1 = $item['feature_1'];
                $t->feature_2 = $item['feature_2'];
                $t->feature_3 = $item['feature_3'];
                $t->save();
            }

            DB::commit();

            return $model;
        } catch(Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            throw new Exception($e->getMessage());
        }
    }

    public function updateLikes(Product $model): Product
    {
        $model->load(['publishedReviews']);

        $model->likes = $model->likes_reviews;
        $model->dislikes = $model->dislikes_reviews;

        $model->save();

        return $model;
    }
}
