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
        Schema::create(CustomerTaxExemption::TABLE, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')
                ->on('customers')
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->date('date_active_to')->nullable();
            $table->string('status');
            $table->string('link', 1024)->nullable();
            $table->string('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(CustomerTaxExemption::TABLE);
    }
};
