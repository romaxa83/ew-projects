<?php

use App\Models\Division;
use App\Models\Partners\Partner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Partner::TABLE, function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->integer('division_id')
                ->nullable()
                ->references('id')
                ->on(Division::TABLE)
                ->onDelete('cascade')
            ;

            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Partner::TABLE);
    }
};
