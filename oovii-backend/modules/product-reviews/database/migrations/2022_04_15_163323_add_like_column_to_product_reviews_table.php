<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLikeColumnToProductReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'product_reviews',
            function (Blueprint $table) {
                $table->dropColumn('likes');
                $table->dropColumn('dislikes');
                $table->boolean('like');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(
            'product_reviews',
            function (Blueprint $table) {
                $table->dropColumn('like');
                $table->unsignedInteger('likes')->default(0);
                $table->unsignedInteger('dislikes')->default(0);
            }
        );
    }
}
