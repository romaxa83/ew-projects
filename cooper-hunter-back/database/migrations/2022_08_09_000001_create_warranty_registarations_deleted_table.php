<?php

use App\Models\Warranty\Deleted\WarrantyRegistrationDeleted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(WarrantyRegistrationDeleted::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->string('warranty_status');
                $table->string('type', 20);
                $table->text('notice')->nullable();

                $table->string('member_type')->nullable();
                $table->unsignedBigInteger('member_id')->nullable();

                $table->foreignId('system_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->json('user_info');
                $table->json('product_info');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(WarrantyRegistrationDeleted::TABLE);
    }
};
