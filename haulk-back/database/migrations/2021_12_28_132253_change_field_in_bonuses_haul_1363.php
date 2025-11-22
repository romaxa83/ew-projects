<?php

use App\Models\Orders\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldInBonusesHaul1363 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropColumn('to');
        });
        Schema::table('bonuses', function (Blueprint $table) {
            $table->enum('to', [Payment::PAYER_CUSTOMER, Payment::PAYER_BROKER, Payment::PAYER_NONE])
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

    }
}
