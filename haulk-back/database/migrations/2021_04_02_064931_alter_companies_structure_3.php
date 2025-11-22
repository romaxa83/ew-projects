<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompaniesStructure3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE companies ALTER usdot TYPE INT USING (trim(usdot)::integer);');
        DB::statement('ALTER TABLE companies
            ALTER COLUMN mc_number TYPE INT USING (trim(mc_number)::integer),
            ALTER COLUMN mc_number DROP NOT NULL,
            ALTER COLUMN mc_number SET DEFAULT NULL;');

        Schema::table('companies', function (Blueprint $table) {
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('usdot')->change();
            $table->string('mc_number')->nullable()->change();

            $table->dropColumn('status');
        });
    }
}
