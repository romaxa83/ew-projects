<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersStructure9 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pickup_contact_id']);
            $table->dropForeign(['delivery_contact_id']);
            $table->dropForeign(['shipper_contact_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('pickup_contact_id');
            $table->dropColumn('delivery_contact_id');
            $table->dropColumn('shipper_contact_id');

            $table->json('pickup_contact')->nullable();
            $table->json('delivery_contact')->nullable();
            $table->json('shipper_contact')->nullable();

            $table->string('pickup_full_name')->nullable();
            $table->string('delivery_full_name')->nullable();
            $table->string('shipper_full_name')->nullable();

            $table->index('pickup_full_name');
            $table->index('delivery_full_name');
            $table->index('shipper_full_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['pickup_full_name']);
            $table->dropIndex(['delivery_full_name']);
            $table->dropIndex(['shipper_full_name']);

            $table->dropColumn('pickup_full_name');
            $table->dropColumn('delivery_full_name');
            $table->dropColumn('shipper_full_name');

            $table->dropColumn('pickup_contact');
            $table->dropColumn('delivery_contact');
            $table->dropColumn('shipper_contact');

            $table->unsignedBigInteger('pickup_contact_id')->nullable();
            $table->unsignedBigInteger('delivery_contact_id')->nullable();
            $table->unsignedBigInteger('shipper_contact_id')->nullable();

            $table->foreign('pickup_contact_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
            $table->foreign('delivery_contact_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
            $table->foreign('shipper_contact_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
        });
    }
}
