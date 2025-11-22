<?php

use App\Models\Dealers\Dealer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(Dealer::TABLE,
            static function (Blueprint $table) {
                $table->dropColumn('description');
                $table->dropColumn('street');
                $table->json('terms')->change();
                $table->string('address_line_1')->after('city');
                $table->string('address_line_2')->after('address_line_1')->nullable();
            }
        );

        Schema::table('dealer_shipping_addresses',
            static function (Blueprint $table) {
                $table->dropColumn('street');
                $table->string('address_line_1')->after('city');
                $table->string('address_line_2')->after('address_line_1')->nullable();
            }
        );

        Schema::table('dealer_contact_accounts',
            static function (Blueprint $table) {
                $table->dropColumn('address');
                $table->string('address_line_1')->after('city');
                $table->string('address_line_2')->after('address_line_1')->nullable();
            }
        );

        Schema::table('dealer_contact_orders',
            static function (Blueprint $table) {
                $table->dropColumn('address');
                $table->string('address_line_1')->after('city');
                $table->string('address_line_2')->after('address_line_1')->nullable();
            }
        );

        Schema::table('dealer_contact_warehouses',
            static function (Blueprint $table) {
                $table->dropColumn('address');
                $table->string('address_line_1')->after('city');
                $table->string('address_line_2')->after('address_line_1')->nullable();
            }
        );
    }

    public function down(): void
    {
        Schema::table(Dealer::TABLE, function (Blueprint $table) {
            $table->mediumText('description')->nullable();
            $table->string('terms', 1000)->change();
            $table->string('street');
            $table->dropColumn('address_line_1');
            $table->dropColumn('address_line_2');
        });

        Schema::table('dealer_shipping_addresses',
            static function (Blueprint $table) {
                $table->string('street');
                $table->dropColumn('address_line_1');
                $table->dropColumn('address_line_2');
            }
        );

        Schema::table('dealer_contact_accounts',
            static function (Blueprint $table) {
                $table->string('address');
                $table->dropColumn('address_line_1');
                $table->dropColumn('address_line_2');
            }
        );

        Schema::table('dealer_contact_orders',
            static function (Blueprint $table) {
                $table->string('address');
                $table->dropColumn('address_line_1');
                $table->dropColumn('address_line_2');
            }
        );

        Schema::table('dealer_contact_warehouses',
            static function (Blueprint $table) {
                $table->string('address');
                $table->dropColumn('address_line_1');
                $table->dropColumn('address_line_2');
            }
        );
    }
};

