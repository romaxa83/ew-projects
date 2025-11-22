<?php

namespace Tests\Unit\Models\Orders\Parts\Order;

use App\Foundations\Entities\Locations\AddressEntity;
use App\Models\Orders\Parts\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\Parts\ItemBuilder;
use Tests\Builders\Orders\Parts\OrderBuilder;
use Tests\TestCase;

class GetTaxTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected ItemBuilder $itemBuilder;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->itemBuilder = resolve(ItemBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function get_tax()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address(AddressEntity::make([
                'first_name' => 'Valerie',
                'last_name' => 'Schinner',
                'company' => 'Haag-Johns',
                'address' => '34295 Gabe Turnpike',
                'city' => 'East Ruthside',
                'state' => 'TX',
                'zip' => '60001',
                'phone' => '1324234234',
            ]))
            ->create();

        $this->itemBuilder->order($model)->price(10)->qty(2)->create();
        $this->itemBuilder->order($model)->price(15)->qty(7)->create();

        $this->assertFalse($model->with_tax_exemption);

        $this->assertEquals(13.13, $model->getTax());
    }

    /** @test */
    public function get_tax_with_delivery_cost()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address(AddressEntity::make([
                'first_name' => 'Valerie',
                'last_name' => 'Schinner',
                'company' => 'Haag-Johns',
                'address' => '34295 Gabe Turnpike',
                'city' => 'East Ruthside',
                'state' => 'TX',
                'zip' => '60001',
                'phone' => '1324234234',
            ]))
            ->create();

        $this->itemBuilder->order($model)->price(10)->qty(2)->delivery_cost(5)->create();
        $this->itemBuilder->order($model)->price(15)->qty(7)->create();

        $this->assertFalse($model->with_tax_exemption);

        $this->assertEquals(14.18, $model->getTax());
    }

    /** @test */
    public function get_tax_with_tax_exemption()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address(AddressEntity::make([
                'first_name' => 'Valerie',
                'last_name' => 'Schinner',
                'company' => 'Haag-Johns',
                'address' => '34295 Gabe Turnpike',
                'city' => 'East Ruthside',
                'state' => 'TX',
                'zip' => '60001',
                'phone' => '1324234234',
            ]))
            ->with_tax_exemption(true)
            ->create();

        $this->itemBuilder->order($model)->price(10)->qty(2)->create();
        $this->itemBuilder->order($model)->price(15)->qty(7)->create();

        $this->assertTrue($model->with_tax_exemption);

        $this->assertEquals(0, $model->getTax());
    }

    /** @test */
    public function get_tax_not_illinois()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address(AddressEntity::make([
                'first_name' => 'Valerie',
                'last_name' => 'Schinner',
                'company' => 'Haag-Johns',
                'address' => '34295 Gabe Turnpike',
                'city' => 'East Ruthside',
                'state' => 'TX',
                'zip' => '60000',
                'phone' => '1324234234',
            ]))
            ->create();

        $this->itemBuilder->order($model)->price(10)->qty(2)->create();
        $this->itemBuilder->order($model)->price(15)->qty(7)->create();

        $this->assertFalse($model->with_tax_exemption);

        $this->assertEquals(0, $model->getTax());
    }

    /** @test */
    public function get_tax_not_billing_address()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address([])
            ->create();

        $this->itemBuilder->order($model)->price(10)->qty(2)->create();
        $this->itemBuilder->order($model)->price(15)->qty(7)->create();

        $this->assertEquals(0, $model->getTax());
    }

    /** @test */
    public function get_tax_not_item()
    {
        /** @var $model Order */
        $model = $this->orderBuilder
            ->billing_address(AddressEntity::make([
                'first_name' => 'Valerie',
                'last_name' => 'Schinner',
                'company' => 'Haag-Johns',
                'address' => '34295 Gabe Turnpike',
                'city' => 'East Ruthside',
                'state' => 'TX',
                'zip' => '60001',
                'phone' => '1324234234',
            ]))
            ->create();

        $this->assertFalse($model->with_tax_exemption);

        $this->assertEquals(0, $model->getTax());
    }
}
