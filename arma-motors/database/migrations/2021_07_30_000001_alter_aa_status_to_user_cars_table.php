<?php

use App\Models\User\Car;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAaStatusToUserCarsTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->integer('aa_status')->default(Car::NONE)->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_cars', function (Blueprint $table) {

        });
    }
}

