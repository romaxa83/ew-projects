<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPayrollsStructureHaul793 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('total', 12, 2)->default(0)->change();
            $table->decimal('subtotal', 12, 2)->default(0)->change();
            $table->decimal('commission', 12, 2)->default(0)->change();
            $table->decimal('salary', 12, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->default(0)->change();
            $table->decimal('subtotal', 10, 2)->default(0)->change();
            $table->decimal('commission', 10, 2)->default(0)->change();
            $table->decimal('salary', 10, 2)->default(0)->change();
        });
    }
}
