<?php

use App\Enums\Orders\OrderDeliveryTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(
            'orders',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('project_id')
                    ->after('technician_id')
                    ->nullable();

                $table->foreign('project_id')
                    ->on('projects')
                    ->references('id')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->dropColumn('address_first_line');
                $table->dropColumn('address_second_line');
                $table->dropColumn('city');
                $table->dropColumn('state');
                $table->dropColumn('zip');
                $table->dropColumn('delivery_type');
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'orders',
            static function (Blueprint $table) {
                $table->dropForeign('orders_project_id_foreign');
                $table->dropColumn('project_id');

                $table->string('address_first_line');
                $table->string('address_second_line')
                    ->nullable();
                $table->string('city');
                $table->string('state', 32);
                $table->string('zip', 32);

                $table->enum('delivery_type', OrderDeliveryTypeEnum::getValues())
                    ->default(OrderDeliveryTypeEnum::GROUND);
            }
        );
    }
};
