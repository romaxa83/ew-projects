<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'rdp_credentials',
            static function (Blueprint $table) {
                $table->id();

                $table->foreignId('credentials_request_id')
                    ->constrained()
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('login');
                $table->string('password', 1000);
                $table->timestamp('start_date');
                $table->timestamp('end_date');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('rdp_credentials');
    }
};
