<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateColumnsInBsInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bs_inventory_transactions', function (Blueprint $table) {
            $table->renameColumn('due_date', 'payment_date');
            $table->string('company_name')->nullable();
            $table->string('payment_method')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bs_inventory_transactions', function (Blueprint $table) {
            $table->renameColumn('payment_date', 'due_date');
            $table->dropColumn('company_name');
            $table->dropColumn('payment_method');
        });
    }
}
