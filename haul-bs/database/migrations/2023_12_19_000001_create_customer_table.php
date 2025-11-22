<?php

use App\Models\Customers\Customer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Customer::TABLE, function (Blueprint $table) {
            $table->id();
            $table->integer('origin_id')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('phone_extension')->nullable();
            $table->json('phones')->nullable();
            $table->string('email');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Customer::TABLE);
    }
};
