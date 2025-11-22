<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterContactsStructure5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('comment_date');
            $table->dropColumn('state');
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('comment_date')->nullable()->after('comment');
            $table->unsignedBigInteger('state_id')->nullable()->after('city');
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
            $table->dropColumn('comment_date');
            $table->dropColumn('state_id');
        });
        Schema::table('contacts', function (Blueprint $table) {
            $table->timestamp('comment_date')->nullable()->after('comment');
            $table->string('state')->nullable()->after('city');
        });
    }
}
