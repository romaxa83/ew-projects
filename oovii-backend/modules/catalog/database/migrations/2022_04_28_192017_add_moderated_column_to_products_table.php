<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Catalog\Models\Product;

class AddModeratedColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            Product::TABLE,
            function (Blueprint $table) {
                $table->boolean('moderated')->default(false);
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
            Product::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('moderated');
            }
        );
    }
}
