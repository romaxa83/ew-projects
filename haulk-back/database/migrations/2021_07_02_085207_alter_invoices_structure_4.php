<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesStructure4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedTinyInteger('attempt')->default(0);
            $table->unsignedBigInteger('last_attempt_time')->nullable();
            $table->string('last_attempt_response')->nullable();
            $table->unsignedBigInteger('next_attempt_time')->default(0);
            $table->json('attempt_history')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('attempt');
            $table->dropColumn('last_attempt_time');
            $table->dropColumn('last_attempt_response');
            $table->dropColumn('next_attempt_time');
            $table->dropColumn('attempt_history');
        });
    }
}
