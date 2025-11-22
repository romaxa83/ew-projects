<?php

use App\Models\Companies\Company;
use App\Models\Customers\Customer;
use App\Models\Vehicles\Trailer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(Trailer::TABLE, function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->nullable()
                ->references('id')
                ->on(Customer::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('vin');
            $table->string('unit_number', 10);
            $table->string('make');
            $table->string('model');
            $table->string('year', 4);
            $table->string('color')->nullable();
            $table->integer('type');
            $table->string('license_plate')->nullable();
            $table->string('temporary_plate')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('company_id')->nullable()
                ->references('id')
                ->on(Company::TABLE)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Trailer::TABLE);
    }
};
