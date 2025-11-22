<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'user_branch',
            static function (Blueprint $table)
            {
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->references('id')
                    ->cascadeOnDelete();
                $table->foreignId('branch_id')
                    ->constrained('branches')
                    ->references('id')
                    ->cascadeOnDelete();

                $table->primary(['user_id', 'branch_id'], 'pk');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('user_branch');
    }
};
