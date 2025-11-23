<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Orders\Models\OrderPaymentInformation;

class AddOrdersColumnToOrderPaymentInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            OrderPaymentInformation::TABLE,
            function (Blueprint $table) {
                $table->string('order_ids')
                    ->unique();
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
            OrderPaymentInformation::TABLE,
            function (Blueprint $table) {
                $table->dropColumn('order_ids');
            }
        );
    }
}
