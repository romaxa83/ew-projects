<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDriverReportsStructureHaul740Part2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_reports', function (Blueprint $table) {
            $table->dropColumn('paid_method_id');
            $table->dropColumn('paid_payment_type');
            $table->dropColumn('paid_payment_days');
            $table->dropColumn('paid_payment_deadline');
            $table->dropColumn('paid_price');
            $table->renameColumn('paid_uship_number', 'uship_number');
            $table->renameColumn('paid_receipt_number', 'receipt_number');
            $table->renameColumn('paid_receipt_date', 'receipt_date');
            $table->renameColumn('paid_invoice_notes', 'invoice_notes');
            $table->json('old_payment_data')->nullable();
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
            $table->unsignedBigInteger('paid_method_id')->nullable();
            $table->string('paid_payment_type')->nullable();
            $table->unsignedSmallInteger('paid_payment_days')->nullable();
            $table->unsignedBigInteger('paid_payment_deadline')->nullable();
            $table->decimal('paid_price', 10, 2)->nullable();
            $table->renameColumn('uship_number', 'paid_uship_number');
            $table->renameColumn('receipt_number', 'paid_receipt_number');
            $table->renameColumn('receipt_date', 'paid_receipt_date');
            $table->renameColumn('invoice_notes', 'paid_invoice_notes');
            $table->dropColumn('old_payment_data');
        });
    }
}
