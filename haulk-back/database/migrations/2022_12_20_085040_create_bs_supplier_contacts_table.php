<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBsSupplierContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bs_supplier_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('phone_extension')->nullable();
            $table->json('phones')->nullable();
            $table->string('email');
            $table->json('emails')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_main')->default(false);
            $table->foreignId('supplier_id')
                ->references('id')->on('bs_suppliers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bs_supplier_contacts', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });
        Schema::dropIfExists('bs_supplier_contacts');
    }
}
