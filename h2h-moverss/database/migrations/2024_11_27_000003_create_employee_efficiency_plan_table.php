<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create(Employee\EfficiencyPlan::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("conversion_local_team")->nullable();
            $table->integer("conversion_long_team")->nullable();
            $table->date("date_at");
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Employee\EfficiencyPlan::TABLE);
    }
};
