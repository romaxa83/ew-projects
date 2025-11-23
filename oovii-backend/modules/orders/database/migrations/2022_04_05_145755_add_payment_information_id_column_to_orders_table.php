<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentInformationIdColumnToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table) {
                $table->foreignId('payment_information_id')
                    ->after('provider_id')
                    ->nullable()
                    ->constrained('order_payment_information')
                    ->nullOnDelete();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(
            'orders',
            function (Blueprint $table) {
                $table->dropForeign(['payment_information_id']);
                $table->dropColumn('payment_information_id');
            }
        );
    }
}
