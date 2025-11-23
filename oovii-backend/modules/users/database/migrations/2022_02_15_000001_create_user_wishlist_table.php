<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Product;
use WezomCms\Users\Models\User;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_wishlist',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')
                    ->references('id')
                    ->on(User::TABLE)
                    ->onDelete('cascade');

                $table->unsignedBigInteger('product_id');
                $table->foreign('product_id')
                    ->references('id')
                    ->on(Product::TABLE)
                    ->onDelete('cascade');

                $table->primary(['user_id', 'product_id']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wishlist');
    }
};
