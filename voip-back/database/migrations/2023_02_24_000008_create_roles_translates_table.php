<?php

use App\Models\Localization\Language;
use App\Models\Permissions\Role;
use App\Models\Permissions\RoleTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(RoleTranslation::TABLE,
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->unsignedBigInteger('row_id')->unsigned();
                $table->string('language', 3);

                $table->foreign('language')
                    ->references('slug')
                    ->on(Language::TABLE)
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('row_id')
                    ->references('id')
                    ->on(Role::TABLE)
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        );

        Role::each(
            function (Role $role) {
                Language::each(
                    function (Language $language) use ($role) {
                        $roleTranslate = new RoleTranslation();
                        $roleTranslate->title = $role->name;
                        $roleTranslate->row_id = $role->id;
                        $roleTranslate->language = $language->slug;
                        $roleTranslate->save();
                    }
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(RoleTranslation::TABLE);
    }
};
