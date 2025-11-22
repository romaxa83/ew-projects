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
            $table->boolean('from_haulk')
                ->after('notes')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table(Customer::TABLE, function (Blueprint $table) {
            $table->dropColumn('from_haulk');
        });
    }
};
