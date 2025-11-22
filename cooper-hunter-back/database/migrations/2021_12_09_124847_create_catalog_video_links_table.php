<?php

use App\Models\Catalog\Products\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('catalog_video_links',
            static function (Blueprint $table) {
                $table->id();
                $table->integer('sort')->default(Product::DEFAULT_SORT);
                $table->boolean('active')->default(Product::DEFAULT_ACTIVE);
                $table->string('link', 300);

                $table->unsignedBigInteger('group_id');
                $table->foreign('group_id')
                    ->on('catalog_video_groups')
                    ->references('id')
                    ->onDelete('cascade')
                    ->cascadeOnUpdate();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_video_links');
    }
};

