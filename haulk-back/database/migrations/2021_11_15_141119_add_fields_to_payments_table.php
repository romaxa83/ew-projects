<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPaymentsTable extends Migration
{
    // all methods
    public const METHOD_COP = 1;
    public const METHOD_COD = 2;
    public const METHOD_COMCHECK = 3;
    public const METHOD_COMPANY_CHECK = 4;
    public const METHOD_ACH = 5;
    public const METHOD_TCH = 6;
    public const METHOD_USHIP = 7;
    public const METHOD_MONEY_ORDER = 8;
    public const METHOD_QUICKPAY = 9;
    public const METHOD_CASHAPP = 10;
    public const METHOD_PAYPAL = 11;
    public const METHOD_VENMO = 12;
    public const METHOD_ZELLE = 13;
    public const METHOD_CASH = 14;
    public const METHOD_CHECK = 15;
    public const METHOD_CERTIFIED_FUNDS = 16;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('price', 'total_carrier_amount');

            $table->decimal('customer_payment_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('customer_payment_method_id')->nullable();
            $table->string('customer_payment_location')->nullable();

            $table->decimal('broker_payment_amount', 10, 2)->nullable();
            $table->renameColumn('method_id', 'broker_payment_method_id');
            $table->unsignedTinyInteger('broker_payment_days')->nullable();
            $table->string('broker_payment_begins')->nullable();

            $table->renameColumn('broker_fee','broker_fee_amount');
            $table->unsignedBigInteger('broker_fee_method_id')->nullable();
            $table->unsignedTinyInteger('broker_fee_days')->nullable();
            $table->string('broker_fee_begins')->nullable();
        });

        DB::statement(sprintf("update payments
        set customer_payment_amount = total_carrier_amount,
        customer_payment_method_id = '%s',
        customer_payment_location = 'pickup',
        broker_payment_method_id = NULL
        where broker_payment_method_id = '%s'
        and payment_type = 'cash';", self::METHOD_CASH, self::METHOD_COP));

        DB::statement(sprintf("update payments
        set customer_payment_amount = total_carrier_amount,
        customer_payment_method_id = '%s',
        customer_payment_location = 'pickup',
        broker_payment_method_id = NULL
        where broker_payment_method_id = '%s'
        and payment_type = 'check';", self::METHOD_CHECK, self::METHOD_COP));

        DB::statement(sprintf("update payments
        set customer_payment_amount = total_carrier_amount,
        customer_payment_method_id = '%s',
        customer_payment_location = 'delivery',
        broker_payment_method_id = NULL
        where broker_payment_method_id = '%s'
        and payment_type = 'cash';", self::METHOD_CASH, self::METHOD_COD));

        DB::statement(sprintf("update payments
        set customer_payment_amount = total_carrier_amount,
        customer_payment_method_id = '%s',
        customer_payment_location = 'delivery',
        broker_payment_method_id = NULL
        where broker_payment_method_id = '%s'
        and payment_type = 'check';", self::METHOD_CHECK, self::METHOD_COD));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_USHIP));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_MONEY_ORDER));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = payment_days,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_COMCHECK));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_method_id = %d,
        broker_payment_days = payment_days,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_COMCHECK, self::METHOD_COMPANY_CHECK));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = payment_days,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_ACH));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = payment_days,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_TCH));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = 2,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_QUICKPAY));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = 2,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_CASHAPP));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = 2,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_PAYPAL));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = 2,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_ZELLE));

        DB::statement(sprintf("update payments
        set broker_payment_amount = total_carrier_amount,
        broker_payment_days = 2,
        broker_payment_begins = 'delivery'
        where broker_payment_method_id = '%s';", self::METHOD_VENMO));

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('old_values');
            $table->dropColumn('payment_deadline');
            $table->dropColumn('paid_method_id');
            $table->dropColumn('payment_type');
            $table->dropColumn('payment_days');
            $table->dropColumn('driver_pay');
            $table->dropColumn('uship_number');
            $table->dropColumn('receipt_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_deadline')->nullable();
            $table->string('payment_type')->nullable();
            $table->unsignedTinyInteger('payment_days')->nullable();
        });

        DB::statement(sprintf("update payments
        set broker_payment_method_id = '%s',
        payment_type = 'cash'
        where customer_payment_method_id = '%s'
        and customer_payment_location = 'pickup';", self::METHOD_COP, self::METHOD_CASH));

        DB::statement(sprintf("update payments
        set broker_payment_method_id = '%s',
        payment_type = 'check'
        where customer_payment_method_id = '%s'
        and customer_payment_location = 'pickup';", self::METHOD_COP, self::METHOD_CHECK));

        DB::statement(sprintf("update payments
        set broker_payment_method_id = '%s',
        payment_type = 'cash'
        where customer_payment_method_id = '%s'
        and customer_payment_location = 'delivery';", self::METHOD_COD, self::METHOD_CASH));

        DB::statement(sprintf("update payments
        set broker_payment_method_id = '%s',
        payment_type = 'check'
        where customer_payment_method_id = '%s'
        and customer_payment_location = 'delivery';", self::METHOD_COD, self::METHOD_CHECK));

        DB::statement(sprintf("update payments
        set payment_days = broker_payment_days
        where broker_payment_method_id = '%s';", self::METHOD_COMCHECK));

        DB::statement(sprintf("update payments
        set payment_days = broker_payment_days
        where broker_payment_method_id = '%s';", self::METHOD_ACH));

        DB::statement(sprintf("update payments
        set payment_days = broker_payment_days
        where broker_payment_method_id = '%s';", self::METHOD_TCH));

        DB::statement(sprintf("update payments
        set payment_deadline = %d
        where broker_payment_method_id = '%s';", time(), self::METHOD_QUICKPAY));

        DB::statement(sprintf("update payments
        set payment_deadline = %d
        where broker_payment_method_id = '%s';", time(), self::METHOD_CASHAPP));

        DB::statement(sprintf("update payments
        set payment_deadline = %d
        where broker_payment_method_id = '%s';", time(), self::METHOD_PAYPAL));

        DB::statement(sprintf("update payments
        set payment_deadline = %d
        where broker_payment_method_id = '%s';", time(), self::METHOD_ZELLE));

        DB::statement(sprintf("update payments
        set payment_deadline = %d
        where broker_payment_method_id = '%s';", time(), self::METHOD_VENMO));

        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('total_carrier_amount', 'price');

            $table->dropColumn('customer_payment_amount');
            $table->dropColumn('customer_payment_method_id');
            $table->dropColumn('customer_payment_location');

            $table->dropColumn('broker_payment_amount');
            $table->renameColumn('broker_payment_method_id', 'method_id');
            $table->dropColumn('broker_payment_days');
            $table->dropColumn('broker_payment_begins');

            $table->renameColumn('broker_fee_amount','broker_fee');
            $table->dropColumn('broker_fee_method_id');
            $table->dropColumn('broker_fee_days');
            $table->dropColumn('broker_fee_begins');

            $table->json('old_values')->nullable();
            $table->unsignedBigInteger('paid_method_id')->nullable();
            $table->decimal('driver_pay', 10, 2)->nullable();
            $table->string('uship_number')->nullable();
            $table->string('receipt_number')->nullable();
        });
    }
}
