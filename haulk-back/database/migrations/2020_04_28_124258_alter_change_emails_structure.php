<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterChangeEmailsStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('change_emails', function (Blueprint $table) {
            $table->dropColumn('token');
            
            $table->string('confirm_token');
            $table->string('decline_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('change_emails', function (Blueprint $table) {
            $table->dropColumn('confirm_token');
            $table->dropColumn('decline_token');

            $table->string('token');
        });
    }
}
