<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterContactsStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->string('full_name')->nullable()->change();

            $table->dropColumn('phone');

            $table->json('phones')->nullable()->after('phone_name');
            $table->boolean('hidden')->default(false)->after('fax'); // for el
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
            $table->dropColumn('hidden');
            $table->dropColumn('phones');

            $table->string('phone')->nullable()->after('phone_name');

            $table->string('full_name')->change();
            $table->bigInteger('user_id')->change();
        });
    }
}
