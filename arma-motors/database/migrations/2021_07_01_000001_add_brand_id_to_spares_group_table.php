<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandIdToSparesGroupTable extends Migration
{
    public function up(): void
    {
        Schema::table('spares_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->foreign('brand_id')
                ->references('id')
                ->on('car_brands')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('spares_groups', function (Blueprint $table) {
            $table->dropForeign('spares_groups_brand_id_foreign');
            $table->dropIndex('spares_groups_brand_id_foreign');
            $table->dropColumn('brand_id');
        });
    }
}
