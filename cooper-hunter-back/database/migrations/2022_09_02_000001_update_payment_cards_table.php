<?php

use App\Models\Payments\PaymentCard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(PaymentCard::TABLE, function (Blueprint $table) {
                $table->string('hash')->unique();
            }
        );
    }

    public function down(): void
    {
        Schema::table(PaymentCard::TABLE, function (Blueprint $table) {
            $table->dropColumn('hash');
        });
    }
};


