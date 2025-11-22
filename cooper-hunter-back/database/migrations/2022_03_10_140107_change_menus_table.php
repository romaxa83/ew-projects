<?php

use App\Enums\Menu\MenuBlockEnum;
use App\Enums\Menu\MenuPositionEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'menus',
            static function (Blueprint $table)
            {
                makeTransaction(
                    static function () use ($table) {
                        $table->dropForeign('menus_parent_id_foreign');
                        $table->dropColumn('parent_id');

                        $table->unsignedBigInteger('page_id')
                            ->after('id');

                        $table->foreign('page_id', 'fk_page_id')
                            ->on('pages')
                            ->references('id')
                            ->cascadeOnDelete()
                            ->cascadeOnUpdate();

                        $table->enum(
                            'position',
                            MenuPositionEnum::getValues()
                        )
                            ->default(MenuPositionEnum::FOOTER)
                            ->change();

                        $table->enum(
                            'block',
                            MenuBlockEnum::getValues()
                        )
                            ->default(MenuBlockEnum::OTHER);
                    }
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'menus',
            static function (Blueprint $table)
            {
                makeTransaction(
                    static function () use ($table) {
                        $table->foreignId('parent_id')
                            ->after('id')
                            ->nullable()
                            ->constrained('menus')
                            ->nullOnDelete()
                            ->cascadeOnUpdate();

                        $table->dropForeign('fk_page_id');
                        $table->dropColumn('page_id');

                        $table->string('position')
                            ->change();

                        $table->dropColumn('block');
                    }
                );
            }
        );
    }
};
