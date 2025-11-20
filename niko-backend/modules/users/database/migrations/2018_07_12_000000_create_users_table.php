<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WezomCms\Users\Models\User;
use WezomCms\Users\Types\UserStatus;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->default(User::DEFAULT_PASSWORD);
            $table->tinyInteger('status')->default(UserStatus::CREATED_NOT_VERIFY);
            $table->string('device_id')->nullable();
            $table->boolean('phone_verified')->default(false)
                ->comment('Потвержден ли телефон');
            $table->string('phone_verify_token')->nullable()
                ->comment('Токен для потверждения');
            $table->timestamp('phone_verify_token_expire')->nullable()
                ->comment('Время жизни токена');
            $table->string('lang',10)->default(config('app.locale'))
                ->comment('Язык пользователя');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
