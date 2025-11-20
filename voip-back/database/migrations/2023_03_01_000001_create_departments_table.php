<?php

use App\Models\Departments\Department;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Department::TABLE,
            static function (Blueprint $table)
            {
                $table->increments('id');

                $table->string('name', 100);
                $table->unsignedInteger('sort')->default(0);
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Department::TABLE);
    }
};
