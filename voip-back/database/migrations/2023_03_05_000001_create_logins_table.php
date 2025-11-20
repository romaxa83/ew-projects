<?php

use App\Models\Logins\Login;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Login::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->numericMorphs('model');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Login::TABLE);
    }
};
