<?php

use App\Models\Companies;
use App\Models\Locations;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('dealer_contact_accounts');
        Schema::dropIfExists('dealer_contact_orders');
        Schema::dropIfExists('dealer_contact_warehouses');
        Schema::dropIfExists('dealer_shipping_addresses');
        Schema::dropIfExists('dealer_prices');
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_contact_accounts');
        Schema::dropIfExists('dealer_contact_orders');
        Schema::dropIfExists('dealer_contact_warehouses');
        Schema::dropIfExists('dealer_shipping_addresses');
        Schema::dropIfExists('dealer_prices');
    }
};

