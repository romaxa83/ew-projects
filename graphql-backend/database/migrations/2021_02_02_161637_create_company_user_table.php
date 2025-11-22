<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'company_user',
            function (Blueprint $table) {
                $table->foreignId('company_id')
                    ->references('id')
                    ->on('companies')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')->index()
                    ->references('id')
                    ->on('users')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('state')->nullable();

                $table->unique(['company_id', 'user_id']);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('company_user');
    }
};
