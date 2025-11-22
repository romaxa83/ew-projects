<?php

use App\Models\Customers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Customers\Address::TABLE, function (Blueprint $table) {
            $table->string('company_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(Customers\Address::TABLE, function (Blueprint $table) {
            $table->dropColumn('company_name');
        });
    }
};
