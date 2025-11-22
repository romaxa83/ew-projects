<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'alert_recipients',
            static function (Blueprint $table)
            {
                $table->unsignedBigInteger('alert_id');
                $table->morphs('recipient');
                $table->boolean('is_read')
                    ->default(false);

                $table->primary(['alert_id', 'recipient_id', 'recipient_type'], 'alert_recipient_key');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_recipients');
    }
};
