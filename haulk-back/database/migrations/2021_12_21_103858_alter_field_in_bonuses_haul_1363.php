<?php

use App\Models\Orders\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFieldInBonusesHaul1363 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropColumn('show_in_invoice');
            $table->enum('to', [Payment::PAYER_CUSTOMER, Payment::PAYER_BROKER])
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            $table->boolean('show_in_invoice')
                ->default(false);
            $table->dropColumn('to');
        });
    }
}
