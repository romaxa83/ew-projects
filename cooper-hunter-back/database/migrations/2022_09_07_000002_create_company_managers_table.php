<?php

use App\Models\Companies\Company;
use App\Models\Companies\Manager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Manager::TABLE,
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('company_id');
                $table->foreign('company_id')
                    ->references('id')
                    ->on(Company::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Manager::TABLE);
    }
};

