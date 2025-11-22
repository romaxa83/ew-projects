<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyNotificationSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');

            $table->json('notification_emails')->nullable();
            $table->json('receive_bol_copy_emails')->nullable();
            $table->boolean('brokers_delivery_notification')->default(false);
            $table->boolean('add_pickup_delivery_dates_to_bol')->default(false);
            $table->boolean('send_bol_invoice_automatically')->default(false);

            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onUpdate('cascade')
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
        Schema::table('company_notification_settings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::dropIfExists('company_notification_settings');
    }
}
