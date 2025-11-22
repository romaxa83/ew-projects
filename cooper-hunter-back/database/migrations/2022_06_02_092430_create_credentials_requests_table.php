<?php

use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'credentials_requests',
            static function (Blueprint $table) {
                $table->id();

                $table->numericMorphs('member', 'member_credentials_morph');

                $table->string('company_name');
                $table->string('company_phone');
                $table->string('company_email');

                $table->enum('status', CommercialCredentialsStatusEnum::getValues())
                    ->default(CommercialCredentialsStatusEnum::NEW);

                $table->foreignId('commercial_project_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('comment', 1000)->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials_requests');
    }
};
