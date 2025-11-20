<?php

use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Localization\Language;
use App\Models\Sips\Sip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(Employee::TABLE,
            static function (Blueprint $table)
            {
                $table->increments('id');

                $table->string('guid', 36)->unique();
                $table->string('status', 15)->nullable();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email', 200)->unique();
                $table->string('password');

                $table->unsignedInteger('department_id');
                $table->foreign('department_id')
                    ->on(Department::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->unsignedInteger('sip_id')->nullable();
                $table->foreign('sip_id')
                    ->on(Sip::TABLE)
                    ->references('id')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();

                $table->string('lang', 4)
                    ->nullable()
                    ->default(app('localization')->getDefaultSlug());
                $table->foreign('lang')
                    ->on(Language::TABLE)
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
        Schema::dropIfExists(Employee::TABLE);
    }
};
