<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTrailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->string('registration_number', 16)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('registration_expiration_date')->nullable();

            $table->string('inspection_number', 16)->nullable();
            $table->date('inspection_date')->nullable();
            $table->date('inspection_expiration_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->dropColumn('registration_number');
            $table->dropColumn('registration_date');
            $table->dropColumn('registration_expiration_date');

            $table->dropColumn('inspection_number');
            $table->dropColumn('inspection_date');
            $table->dropColumn('inspection_expiration_date');
        });
    }
}
