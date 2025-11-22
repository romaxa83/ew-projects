<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeFieldsToSparesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('spares', function (Blueprint $table) {
            $table->integer('qty')->default(\App\Models\Catalogs\Calc\Spares::DEFAULT_QTY);
            $table->bigInteger('discount_price')->nullable();

            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')
                ->references('id')
                ->on('spares_groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('spares', function (Blueprint $table) {
            $table->dropColumn('qty');
            $table->dropColumn('discount_price');

            $table->dropForeign('spares_group_id_foreign');
            $table->dropIndex('spares_group_id_foreign');
            $table->dropColumn('group_id');
        });
    }
}
