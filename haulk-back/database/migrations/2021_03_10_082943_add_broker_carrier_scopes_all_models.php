<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrokerCarrierScopesAllModels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('broker_id');
            $table->index('carrier_id');
        });

        Schema::table('library_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('carrier_id');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('carrier_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('broker_id');
            $table->index('carrier_id');
        });

        Schema::table('order_comments', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('carrier_id');
        });

        Schema::table('questions_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('carrier_id');
        });

        Schema::table('driver_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('carrier_id');
        });

        Schema::table('change_emails', function (Blueprint $table) {
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('broker_id');
            $table->index('carrier_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('broker_id')->nullable();
            $table->unsignedBigInteger('carrier_id')->nullable();

            $table->index('broker_id');
            $table->index('carrier_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });

        Schema::table('library_documents', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        /*Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });*/

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });

        Schema::table('order_comments', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        /*Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });*/

        Schema::table('questions_answers', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        Schema::table('driver_reports', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });

        Schema::table('change_emails', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });

        /*Schema::table('driver_information', function (Blueprint $table) {
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('carrier_id');
        });*/

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['broker_id']);
            $table->dropIndex(['carrier_id']);

            $table->dropColumn('broker_id');
            $table->dropColumn('carrier_id');
        });
    }
}
