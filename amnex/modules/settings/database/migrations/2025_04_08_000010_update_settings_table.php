<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Wezom\Settings\Models\Setting;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table(Setting::TABLE, function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('group_title')->nullable();
            $table->string('type', 15)->default(Setting::TYPE_STRING);
        });
    }

    public function down(): void
    {
        Schema::table(Setting::TABLE, function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'group_title',
                'type'
            ]);
        });
    }
};
