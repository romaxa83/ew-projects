<?php

use App\Models\Vapi\ClientRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ClientRequest::TABLE, function (Blueprint $table) {

            $table->string('call_back_at')
                ->change()
                ->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('pickup_stairs')->nullable();
            $table->string('delivery_location')->nullable();
            $table->string('delivery_stairs')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(ClientRequest::TABLE, function (Blueprint $table) {

            $table->dropColumn([
                'pickup_location',
                'pickup_stairs',
                'delivery_location',
                'delivery_stairs',
            ]);
        });
    }
};
