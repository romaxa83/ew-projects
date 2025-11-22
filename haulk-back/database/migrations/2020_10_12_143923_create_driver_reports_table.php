<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('load_id');
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('delivery_date_actual');
            $table->unsignedBigInteger('pickup_state_id');
            $table->string('pickup_zip');
            $table->unsignedBigInteger('delivery_state_id');
            $table->string('delivery_zip');
            $table->string('company_name');
            $table->json('vehicles');
            $table->unsignedBigInteger('payment_method_id');
            $table->enum('payment_type', ['cash', 'check'])->nullable();
            $table->unsignedTinyInteger('payment_days')->nullable();
            $table->unsignedBigInteger('payment_deadline')->nullable();
            $table->decimal('total', 10, 2);

            $table->index('driver_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_reports', function (Blueprint $table) {
            $table->dropIndex(['driver_id']);
        });
        Schema::dropIfExists('driver_reports');
    }
}
