<?php

use App\Models\Payments\PaymentCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(PaymentCard::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->uuid('guid')->nullable()->unique();

                $table->morphs('member');

                $table->string('code', 4);
                $table->string('type', 100);
                $table->string('expiration_date', 30);
                $table->boolean('default');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(PaymentCard::TABLE);
    }
};

