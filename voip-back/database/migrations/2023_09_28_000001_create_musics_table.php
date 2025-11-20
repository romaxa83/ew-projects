<?php

use App\Models\Departments\Department;
use App\Models\Musics\Music;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Music::TABLE,
            static function (Blueprint $table) {
                $table->id();
                $table->boolean('active')->default(true);
                $table->unsignedInteger('interval')->default(0);

                $table->unsignedInteger('department_id');
                $table->foreign('department_id')
                    ->on(Department::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(Music::TABLE);
    }
};

