<?php

use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Dealer::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->string('status', 15)->default('draft');
                $table->string('type', 30);
                $table->string('code', 36)
                    ->nullable()->unique('dealers_code_unique');
                $table->string('terms', 1000)->nullable();

                $table->string('business_name');
                $table->string('password')->nullable();

                $table->string('email')->nullable()->unique();
                $table->string('contact_email')->unique();
                $table->string('phone', 24)->nullable()->unique();
                $table->unsignedInteger('state_id');
                $table->foreign('state_id')
                    ->references('id')
                    ->on(State::TABLE)
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->string('city');
                $table->string('street');
                $table->string('po_box');
                $table->string('zip');
                $table->string('fax')->unique();
                $table->string('taxpayer_id')->nullable()->unique();
                $table->string('tax')->nullable();
                $table->json('sites')->nullable();
                $table->json('links')->nullable();
                $table->json('trades')->nullable();
                $table->mediumText('description')->nullable();

                $table->timestamp('email_verified_at')->nullable();
                $table->timestamp('phone_verified_at')->nullable();
                $table->string('email_verification_code', 16)->nullable();
                $table->rememberToken();

                $table->string('lang')->nullable();
                $table->foreign('lang')
                    ->on('languages')
                    ->references('slug')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->softDeletes();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Dealer::TABLE);
    }
};



