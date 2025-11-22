<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIconServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('for_guest')->default(false)->comment('Виден ли для гостя');
            $table->string('icon', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('for_guest');
            $table->dropColumn('icon');
        });
    }
}
