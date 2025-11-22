<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'warranty_registrations',
            static function (Blueprint $table) {
                $table->id();

                $table->morphs('member');

                $table->foreignId('system_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->json('user_info');
                $table->json('address_info');
                $table->json('product_info');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_registrations');
    }
};
