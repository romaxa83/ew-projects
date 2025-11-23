<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Providers\Models\Provider;
use WezomCms\Providers\Types\ProviderStatus;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Provider::TABLE,
            static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->tinyInteger('status')->default(ProviderStatus::DRAFT);
                $table->string('name')->nullable();
                $table->string('phone')->nullable()->unique();
                $table->boolean('phone_verified')->default(false);
                $table->string('email')->nullable()->unique();
                $table->boolean('email_verified')->default(false);
                $table->string('password');
                $table->boolean('active')->default(true)->index();

                $table->unsignedBigInteger('company_id')->nullable();
                $table->foreign('company_id')
                    ->references('id')
                    ->on('companies')
                    ->onDelete('cascade');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Provider::TABLE);
    }
};

