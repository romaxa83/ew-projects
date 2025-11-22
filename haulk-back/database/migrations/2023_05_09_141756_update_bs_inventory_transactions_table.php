<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBsInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bs_inventory_transactions', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->change();
            $table->decimal('discount')->nullable();
            $table->decimal('tax')->nullable();
            $table->date('due_date')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->renameColumn('comment', 'describe');
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
            $table->string('invoice_number')->nullable(false)->change();
            $table->dropColumn('discount');
            $table->dropColumn('tax');
            $table->dropColumn('due_date');
            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('phone');
            $table->dropColumn('email');
            $table->renameColumn('describe', 'comment');
        });
    }
}
