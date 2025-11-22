<?php

use App\Models\Support\SupportRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'support_request_messages',
            static function (Blueprint $table)
            {
                $table->id();
                $table->unsignedBigInteger('support_request_id');
                $table->text('message');
                $table->morphs('sender');
                $table->timestamps();

                $table->foreign('support_request_id')
                    ->on(SupportRequest::TABLE)
                    ->references('id')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('support_request_messages');
    }
};
