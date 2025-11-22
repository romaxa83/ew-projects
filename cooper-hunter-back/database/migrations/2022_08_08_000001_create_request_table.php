<?php

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialProjectUnit;
use App\Models\Request\Request;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Request::TABLE, function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('driver');
            $table->string('command');
            $table->string('url');
            $table->string('status');
            $table->jsonb('send_data')->nullable();
            $table->jsonb('response_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Request::TABLE);
    }
};


