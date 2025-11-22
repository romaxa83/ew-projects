<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOwnerIdColumnInTrucksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->bigInteger('owner_id')->nullable()->change();
            $table->renameColumn('owner_id', 'customer_id');
            $table->dropForeign('trucks_owner_id_foreign');
            $table->foreign('customer_id')
                ->references('id')->on('bs_vehicle_owners')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
        Schema::table('trucks', function (Blueprint $table) {
            $table->foreignId('owner_id')
                ->nullable()
                ->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trucks', function (Blueprint $table) {
            $table->dropColumn('owner_id');
            $table->dropForeign('trucks_customer_id_foreign');
        });
        Schema::table('trucks', function (Blueprint $table) {
            $table->renameColumn('customer_id', 'owner_id');
            $table->foreign('owner_id')
                ->references('id')->on('bs_vehicle_owners')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }
}
