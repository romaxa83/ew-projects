<?php

use App\Models\Agreement\Agreement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Agreement::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->tinyInteger('status')->default(Agreement::STATUS_NEW);
                $table->string('uuid');
                $table->string('user_uuid');
                $table->string('car_uuid');
                $table->string('phone');
                $table->string('number');
                $table->string('vin');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Agreement::TABLE);
    }
};
