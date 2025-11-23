<?php

use App\Models\Vapi\ClientRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(ClientRequest::TABLE, function (Blueprint $table) {
            $table->string('additional')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table(ClientRequest::TABLE, function (Blueprint $table) {

            $table->dropColumn([
                'additional'
            ]);
        });
    }
};
