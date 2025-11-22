<?php

use App\Models\Customers\CustomerTaxExemption;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(CustomerTaxExemption::TABLE, function (Blueprint $table) {
            $table->string('file_name', 1024)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CustomerTaxExemption::TABLE, function (Blueprint $table) {
            $table->dropColumn('file_name');
        });
    }
};
