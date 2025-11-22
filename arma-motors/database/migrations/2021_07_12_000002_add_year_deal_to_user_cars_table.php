<?php

use App\Models\User\Car;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddYearDealToUserCarsTable extends Migration
{
    public function up(): void
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->year('year_deal')->after('year')->nullable();
            $table->year('aa_status')->after('inner_status')->default(Car::NONE);
            $table->boolean('is_order')->default(false);
            $table->boolean('in_garage')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('user_cars', function (Blueprint $table) {
            $table->dropColumn('year_deal');
            $table->dropColumn('aa_status');
            $table->dropColumn('is_order');
            $table->dropColumn('in_garage');
        });
    }
}
