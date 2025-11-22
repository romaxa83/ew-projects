<?php


namespace Tests\Feature\Api\Carrier;


use App\Models\Saas\Company\Company;
use Illuminate\Foundation\Console\QueuedCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class CarrierDeleteTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_send_destroy_notification(): Company
    {
        $user = $this->loginAsCarrierSuperAdmin();
        $company = $user->getCompany();

        $this->postJson(
            route('carrier.send-destroy-notification')
        )->assertNoContent();

        $company->refresh();

        $this->assertNotNull($company->crm_decline_token);
        $this->assertNotNull($company->crm_confirm_token);
        $this->assertNotNull($company->crm_date_token_create);
        return $company;
    }

    public function test_token_expired()
    {
        $company = $this->test_send_destroy_notification();

        $company->crm_date_token_create = Carbon::now()->subMonth()->toDateTimeString();

        $company->save();

        $this->postJson(
            route('carrier.set-destroy'),
            [
                'token' => $company->crm_confirm_token,
                'type' => 'confirm'
            ]
        )->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_decline_delete()
    {
        $company = $this->test_send_destroy_notification();

        $this->postJson(
            route('carrier.set-destroy'),
            [
                'token' => $company->crm_decline_token,
                'type' => 'decline'
            ]
        )->assertNoContent();

        $company->refresh();

        $this->assertNull($company->crm_decline_token);
        $this->assertNull($company->crm_confirm_token);
        $this->assertNull($company->crm_date_token_create);
    }

    public function test_confirm_delete()
    {
        Queue::fake();

        $company = $this->test_send_destroy_notification();

        Queue::assertNotPushed(QueuedCommand::class);

        $this->postJson(
            route('carrier.set-destroy'),
            [
                'token' => $company->crm_confirm_token,
                'type' => 'confirm'
            ]
        )->assertNoContent();

        $company->refresh();

        $this->assertNull($company->crm_decline_token);
        $this->assertNull($company->crm_confirm_token);
        $this->assertNull($company->crm_date_token_create);

        Queue::assertPushed(QueuedCommand::class);
    }
}
