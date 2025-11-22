<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompanyPaymentMethods2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_payment_methods', function (Blueprint $table) {
            $table->string('payment_provider')->nullable();
            $table->json('payment_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_payment_methods', function (Blueprint $table) {
            $table->dropColumn('payment_provider');
            $table->dropColumn('payment_data');
        });
    }
}
