<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCompanyBillingContactsStructure2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_billing_contacts', function (Blueprint $table) {
            $table->boolean('use_accounting_contact')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_billing_contacts', function (Blueprint $table) {
            $table->dropColumn('use_accounting_contact');
        });
    }
}
