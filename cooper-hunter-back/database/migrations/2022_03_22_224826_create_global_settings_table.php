<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'global_settings',
            static function (Blueprint $table) {
                $table->id();
                $table->string('footer_address');
                $table->string('footer_email');
                $table->string('footer_phone');
                $table->string('footer_instagram_link');
                $table->string('footer_meta_link');
                $table->string('footer_twitter_link');
                $table->string('footer_youtube_link');
                $table->string('footer_description');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
