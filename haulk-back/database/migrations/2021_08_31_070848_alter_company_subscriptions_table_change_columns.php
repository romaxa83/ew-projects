<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompanySubscriptionsTableChangeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE company_subscriptions
            ALTER COLUMN billing_start TYPE TIMESTAMP,
            ALTER COLUMN billing_end TYPE TIMESTAMP;');

        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->dropColumn('expires_at');

            $table->boolean('is_trial')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_trial');

            $table->unsignedBigInteger('expires_at')->nullable();

            $table->date('billing_start')->nullable()->change();
            $table->date('billing_end')->nullable()->change();
        });
    }
}
