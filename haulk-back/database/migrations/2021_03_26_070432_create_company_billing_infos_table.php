<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyBillingInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');

            $table->string('billing_phone')->nullable();
            $table->string('billing_phone_name')->nullable();
            $table->string('billing_phone_extension')->nullable();
            $table->json('billing_phones')->nullable();
            $table->string('billing_email')->nullable();
            $table->text('billing_payment_details')->nullable();
            $table->text('billing_terms')->nullable();

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
        Schema::table('company_billing_infos', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
        });

        Schema::dropIfExists('company_billing_infos');
    }
}
