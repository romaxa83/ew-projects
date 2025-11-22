<?php

use App\Models\Customers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Customers\Address::TABLE, function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')->nullable()
                ->references('id')
                ->on(Customers\Customer::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('type', 20)->default(Customers\Address::DEFAULT_TYPE);
            $table->boolean('is_default')->default(false);
            $table->boolean('from_ecomm')->default(false);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('phone');
            $table->integer('sort')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Customers\Address::TABLE);
    }
};

