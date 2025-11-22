<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDriverReportsStructureHaul740 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('paid_method_id')->nullable();
            $table->string('paid_payment_type')->nullable();
            $table->unsignedSmallInteger('paid_payment_days')->nullable();
            $table->unsignedBigInteger('paid_payment_deadline')->nullable();
            $table->decimal('paid_price', 10, 2)->nullable();
            $table->string('paid_uship_number')->nullable();
            $table->string('paid_receipt_number')->nullable();
            $table->bigInteger('paid_receipt_date')->nullable();
            $table->text('paid_invoice_notes')->nullable();
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
            $table->dropColumn('paid_method_id');
            $table->dropColumn('paid_payment_type');
            $table->dropColumn('paid_payment_days');
            $table->dropColumn('paid_payment_deadline');
            $table->dropColumn('paid_price');
            $table->dropColumn('paid_uship_number');
            $table->dropColumn('paid_receipt_number');
            $table->dropColumn('paid_receipt_date');
            $table->dropColumn('paid_invoice_notes');
        });
    }
}
