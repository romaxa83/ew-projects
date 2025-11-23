<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Product;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');

                $table->dropForeign(['model_id']);
                $table->dropColumn('model_id');

                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');

                $table->dropColumn('discount_percentage');
                $table->dropColumn('old_cost');
                $table->dropColumn('videos');

                $table->timestamp('published_at')->default(Carbon::now());
                $table->float('cost_discount', 10, 2)
                    ->after('cost')
                    ->default(0)
                    ->index();
                $table->integer('amount')->default(0);
                $table->integer('amount_one_user')->default(0);

                $table->unsignedBigInteger('provider_id')
                    ->after('id')
                    ->nullable();
                $table->foreign('provider_id')
                    ->references('id')
                    ->on('administrators')
                    ->onDelete('cascade');

                $table->unsignedBigInteger('moderator_id')
                    ->after('id')
                    ->nullable();
                $table->foreign('moderator_id')
                    ->references('id')
                    ->on('administrators');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Product::TABLE,
            static function (Blueprint $table) {
                $table->foreignId('brand_id')
                    ->after('published')
                    ->nullable()->constrained()
                    ->nullOnDelete();

                $table->foreignId('model_id')
                    ->after('published')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('category_id')
                    ->after('published')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->float('old_cost', 10, 2)->default(0);
                $table->json('videos')->nullable(false);
                $table->unsignedSmallInteger('discount_percentage')->nullable();

                $table->dropColumn('published_at');
                $table->dropColumn('cost_discount');
                $table->dropColumn('amount');
                $table->dropColumn('amount_one_user');

                $table->dropForeign('products_moderator_id_foreign');
                $table->dropColumn('moderator_id');

                $table->dropForeign('products_provider_id_foreign');
                $table->dropColumn('provider_id');
            }
        );
    }
};
