<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Collections\Category;
use WezomCms\Catalog\Models\Collections\Collection;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('gender');
                $table->dropColumn('group');

                $table->unsignedBigInteger('category_id')->nullable();
                $table->foreign('category_id')
                    ->references('id')
                    ->on(Category::TABLE)
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::table(Collection::TABLE,
            static function (Blueprint $table) {
                $table->string('gender', 20)->nullable();
                $table->string('group', 500)->nullable();
                $table->dropForeign('collections_category_id_foreign');
                $table->dropColumn('category_id');
            }
        );
    }
};

