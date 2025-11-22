<?php

namespace Tests\Feature\Saas\Invoices;

use App\Models\Billing\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Billing\InvoiceBuilder;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use DatabaseTransactions;

    private Invoice $invoice;
    protected InvoiceBuilder $invoiceBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->invoice = factory(Invoice::class)->create();
        $this->invoiceBuilder = resolve(InvoiceBuilder::class);

        $this->loginAsSaasSuperAdmin();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_companies_list()
    {
        $this->getJson(
            route('v1.saas.invoices.companies-list')
        )
            ->assertOk()
            ->assertJsonStructure(['data' => ['*' => ['company_id', 'company_name']]])
            ->assertJson([
                'data' => [
                    [
                        'company_id' => $this->invoice->carrier_id,
                        'company_name' => $this->invoice->company_name
                    ]
                ]
            ]);
    }

    public function test_get_invoices()
    {
        $this->getJson(
            route('v1.saas.invoices.index')
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'company_name',
                        'billing_start',
                        'billing_end',
                        'amount',
                        'pending',
                        'trans_id',
                        'is_paid',
                        'paid_at',
                        'public_token'
                    ]
                ],
                'links',
                'meta'
            ])
            ->assertJsonFragment([
                'data' => [
                    [
                        'id' => $this->invoice->id,
                        'company_name' => $this->invoice->company_name,
                        'billing_start' => strtotime($this->invoice->billing_start),
                        'billing_end' => strtotime($this->invoice->billing_end),
                        'amount' => $this->invoice->amount,
                        'pending' => $this->invoice->pending,
                        'trans_id' => $this->invoice->trans_id,
                        'is_paid' => $this->invoice->is_paid,
                        'paid_at' => $this->invoice->paid_at,
                        'public_token' => $this->invoice->public_token
                    ]
                ]
            ]);
    }

    /** @test */
    public function filter_has_gps_subscription()
    {
        $m_1 = $this->invoiceBuilder->hasGpsSubscription()->create();
        $m_2 = $this->invoiceBuilder->hasGpsSubscription()->create();
        $m_3 = $this->invoiceBuilder->create();

        $this->getJson(route('v1.saas.invoices.index', [
            'has_gps_subscription' => true
        ]))
            ->assertOk()
            ->assertJson([
                'meta' => [
                    'total' => 2
                ]
            ]);
    }

}
