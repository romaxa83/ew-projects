<?php

use App\Models\Agreement\Agreement;
use App\Models\Agreement\Job;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Job::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');

                $table->string('name');
                $table->string('sum');

                $table->unsignedBigInteger('agreement_id');
                $table->foreign('agreement_id')
                    ->references('id')
                    ->on(Agreement::TABLE)
                    ->onDelete('cascade');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Job::TABLE);
    }
};
