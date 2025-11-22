<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceInsuranceFranchiseRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_insurance_franchise_relation', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');
            $table->unsignedBigInteger('franchise_id');
            $table->foreign('franchise_id')
                ->references('id')
                ->on('insurance_franchise')
                ->onDelete('cascade');

            $table->primary(['service_id', 'franchise_id'], 'pk-sifr_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_insurance_franchise_relation');
    }
}

