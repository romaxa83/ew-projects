<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPaymentsStructure7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()
            ->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('driver_payment_status');
            $table->dropColumn('driver_received_amount');
            $table->dropColumn('driver_not_received_comment');
            $table->dropColumn('driver_cashless_method');
            $table->dropColumn('driver_cashless_amount');
            $table->dropColumn('driver_cashless_timestamp');

            $table->boolean('driver_payment_data_sent')->default(false);
            $table->enum('driver_payment_received', ['received', 'not_received'])->nullable();
            $table->decimal('driver_payment_amount', 10, 2)->nullable();
            $table->string('driver_payment_uship_code')->nullable();
            $table->string('driver_payment_comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::connection()
            ->getDoctrineSchemaManager()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping('enum', 'string');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('driver_payment_data_sent');
            $table->dropColumn('driver_payment_received');
            $table->dropColumn('driver_payment_amount');
            $table->dropColumn('driver_payment_uship_code');
            $table->dropColumn('driver_payment_comment');
            
            $table->tinyInteger('driver_payment_status')->nullable();
            $table->decimal('driver_received_amount', 10, 2)->nullable();
            $table->string('driver_not_received_comment')->nullable();
            $table->unsignedBigInteger('driver_cashless_method')->nullable();
            $table->decimal('driver_cashless_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('driver_cashless_timestamp')->nullable();
        });
    }
}
