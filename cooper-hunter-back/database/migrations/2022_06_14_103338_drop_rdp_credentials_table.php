<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('rdp_credentials');
    }

    public function down(): void
    {
        Schema::create(
            'rdp_credentials',
            static function (Blueprint $table) {
                $table->id();

                $table->foreignId('credentials_request_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->foreignId('rdp_account_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('login');
                $table->string('password', 1000);
                $table->timestamp('start_date');
                $table->timestamp('end_date');

                $table->timestamps();
            }
        );
    }
};
