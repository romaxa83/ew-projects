<?php

use App\Models\Sips\Sip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Sip::TABLE,
            static function (Blueprint $table)
            {
                $table->increments('id');

                $table->string('number', 10)->unique();
                $table->string('password');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Sip::TABLE);
    }
};
