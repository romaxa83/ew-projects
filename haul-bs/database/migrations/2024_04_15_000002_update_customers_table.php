<?php

use App\Models\Customers\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(Customer::TABLE, function (Blueprint $table) {
            $table->boolean('has_ecommerce_account')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Customer::TABLE, function (Blueprint $table) {
            $table->dropColumn('has_ecommerce_account');
        });
    }
};
