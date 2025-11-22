<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'rdp_accounts',
            static function (Blueprint $table) {
                $table->id();
                $table->numericMorphs('member');

                $table->string('login')->unique();
                $table->string('password', 1000);

                $table->boolean('active')->default(true);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('rdp_accounts');
    }
};
