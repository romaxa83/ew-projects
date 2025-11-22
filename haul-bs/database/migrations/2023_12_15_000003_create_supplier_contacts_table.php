<?php

use App\Models\Suppliers\Supplier;
use App\Models\Suppliers\SupplierContact;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(SupplierContact::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('phone');
            $table->string('phone_extension')->nullable();
            $table->json('phones')->nullable();
            $table->string('email');
            $table->json('emails')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_main')->default(false);

            $table->foreignId('supplier_id')
                ->references('id')
                ->on(Supplier::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(SupplierContact::TABLE);
    }
};
