<?php

namespace Tests\Feature\Api\Orders;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class OrderPublicPdfBolTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_it_show_public_pdf_bol_success()
    {
        $order = $this->orderFactory();

        $response = $this->getJson(route('orders.public.pdf-bol', ['token' => $order->getPublicToken()]))
            ->assertOk();

        $response->assertHeader('Content-Disposition', "attachment; filename=bol.pdf");
    }
}
