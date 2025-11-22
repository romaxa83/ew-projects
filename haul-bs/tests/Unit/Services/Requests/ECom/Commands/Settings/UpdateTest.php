<?php

namespace Tests\Unit\Services\Requests\ECom\Commands\Settings;

use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Modules\Media\Traits\TransformFullUrl;
use App\Repositories\Settings\SettingRepository;
use App\Services\Requests\ECom\Commands\Settings\SettingsUpdateCommand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\SettingsData;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use TransformFullUrl;
    use SettingsData;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_prepare_data()
    {
        $settingData = $this->setSettings();
        $repo = resolve(SettingRepository::class);

        /** @var $command SettingsUpdateCommand */
        $command = resolve(SettingsUpdateCommand::class);

        $res = $command->beforeRequestForData($repo->getInfoForEcomm());

        $this->assertEquals($res['ecommerce_company_name'], $settingData['ecommerce_company_name']);
        $this->assertEquals($res['ecommerce_address'], $settingData['ecommerce_address']);
        $this->assertEquals($res['ecommerce_city'], $settingData['ecommerce_city']);
        $this->assertEquals($res['ecommerce_state_name'], State::find($settingData['ecommerce_state_id'])->name);
        $this->assertEquals($res['ecommerce_zip'], $settingData['ecommerce_zip']);
        $this->assertEquals($res['ecommerce_phone'], $settingData['ecommerce_phone']);
        $this->assertEquals($res['ecommerce_phone_name'], $settingData['ecommerce_phone_name']);
        $this->assertEquals($res['ecommerce_phone_extension'], $settingData['ecommerce_phone_extension']);
//        $this->assertEquals($res['ecommerce_phones'], $settingData['ecommerce_phones']);
        $this->assertEquals($res['ecommerce_email'], $settingData['ecommerce_email']);
        $this->assertEquals($res['ecommerce_fax'], $settingData['ecommerce_fax']);
        $this->assertEquals($res['ecommerce_website'], $settingData['ecommerce_website']);
        $this->assertEquals($res['ecommerce_billing_phone'], $settingData['ecommerce_billing_phone']);
        $this->assertEquals($res['ecommerce_billing_phone_name'], $settingData['ecommerce_billing_phone_name']);
        $this->assertEquals($res['ecommerce_billing_phone_extension'], $settingData['ecommerce_billing_phone_extension']);
//        $this->assertEquals($res['ecommerce_billing_phones'], $settingData['ecommerce_billing_phones']);
        $this->assertEquals($res['ecommerce_billing_email'], $settingData['ecommerce_billing_email']);
        $this->assertEquals($res['ecommerce_billing_payment_details'], $settingData['ecommerce_billing_payment_details']);
        $this->assertEquals($res['ecommerce_billing_terms'], $settingData['ecommerce_billing_terms']);
    }

    /** @test */
    public function check_uri()
    {
        $settingData = $this->setSettings();

        /** @var $command SettingsUpdateCommand */
        $command = resolve(SettingsUpdateCommand::class);

        $this->assertEquals(
            $command->getUri($command->beforeRequestForData($settingData)),
            config("requests.e_com.paths.settings.update")
        );
    }
}
