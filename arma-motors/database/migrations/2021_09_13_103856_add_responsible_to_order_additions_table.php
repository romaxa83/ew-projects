<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResponsibleToOrderAdditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->string('responsible')
                ->nullable()
                ->after('dealership_id')
                ->comment('Ответственно лицо');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_additions', function (Blueprint $table) {
            $table->dropColumn('responsible');
        });
    }
}
