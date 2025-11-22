<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'clients',
            static function (Blueprint $table)
            {
                $table->id();
                $table->string('name');
                $table->string('contact_person');
                $table->foreignId('manager_id')
                    ->constrained('managers')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->string('edrpou')
                    ->nullable()
                    ->unique();
                $table->string('ban_reason')
                    ->nullable();
                $table->boolean('show_ban_in_inspection')
                    ->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
