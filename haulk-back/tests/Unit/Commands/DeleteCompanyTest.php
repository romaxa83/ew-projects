<?php


namespace Commands;


use App\Console\Commands\DeleteCompany;
use App\Models\Contacts\Contact;
use App\Models\Library\LibraryDocument;
use App\Models\Lists\BonusType;
use App\Models\Lists\ExpenseType;
use App\Models\News\News;
use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\Vehicle;
use App\Models\Payrolls\Payroll;
use App\Models\QuestionAnswer\QuestionAnswer;
use App\Models\Reports\DriverTripReport;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DeleteCompanyTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_company_delete(): void
    {
        // create company with superadmin
        $company = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.trial.slug'));
        $this->assertNotEquals($company->id, config('haulk.id'));

        // create order
        $order = $this->createOrder($company);
        $this->assertDatabaseHas(Order::TABLE_NAME, ['id' => $order->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseHas(Payment::TABLE_NAME, ['id' => $order->payment->id, 'order_id' => $order->id]);
        $this->assertDatabaseHas(Expense::TABLE_NAME, ['id' => $order->expenses[0]->id, 'order_id' => $order->id]);
        $this->assertDatabaseHas(Bonus::TABLE_NAME, ['id' => $order->bonuses[0]->id, 'order_id' => $order->id]);
        $this->assertDatabaseHas(Vehicle::TABLE_NAME, ['id' => $order->vehicles[0]->id, 'order_id' => $order->id]);
        $this->assertDatabaseHas(Inspection::TABLE_NAME, ['id' => $order->vehicles[0]->pickupInspection->id]);
        $this->assertDatabaseHas(Inspection::TABLE_NAME, ['id' => $order->vehicles[0]->deliveryInspection->id]);

        // check order media
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $order->id]);
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $order->vehicles[0]->pickupInspection->id]);

        // create news
        $news = $this->createNews($company->id);
        $this->assertDatabaseHas(News::TABLE_NAME, ['id' => $news->id, 'carrier_id' => $company->id]);

        // create driver trip report
        $driverTripReport = $this->createDriverTripReport($company->id);
        $this->assertDatabaseHas(DriverTripReport::TABLE_NAME, ['id' => $driverTripReport->id, 'carrier_id' => $company->id]);

        // create payroll
        $payroll = $this->createPayroll($company->id);
        $this->assertDatabaseHas(Payroll::TABLE_NAME, ['id' => $payroll->id, 'carrier_id' => $company->id]);

        // create faq
        $questionAnswer = $this->createQuestionAnswer($company->id);
        $this->assertDatabaseHas(QuestionAnswer::TABLE_NAME, ['id' => $questionAnswer->id, 'carrier_id' => $company->id]);

        // create contact
        $contact = $this->createContact($company->id);
        $this->assertDatabaseHas(Contact::TABLE_NAME, ['id' => $contact->id, 'carrier_id' => $company->id]);

        // create library
        $libraryDocument = $this->createLibraryDocument($company->id);
        $this->assertDatabaseHas(LibraryDocument::TABLE_NAME, ['id' => $libraryDocument->id, 'carrier_id' => $company->id]);

        // create bonus list
        $bonusType = $this->createBonusType($company->id);
        $this->assertDatabaseHas(BonusType::TABLE_NAME, ['id' => $bonusType->id, 'carrier_id' => $company->id]);

        // create expense list
        $expenseType = $this->createExpenseType($company->id);
        $this->assertDatabaseHas(ExpenseType::TABLE_NAME, ['id' => $expenseType->id, 'carrier_id' => $company->id]);

        // call delete
        $this->artisan(DeleteCompany::class, ['company_id' => $company->id])
            ->assertExitCode(0);

        // check db has no items
        $this->assertDatabaseMissing(Order::TABLE_NAME, ['id' => $order->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(Payment::TABLE_NAME, ['id' => $order->payment->id, 'order_id' => $order->id]);
        $this->assertDatabaseMissing(Expense::TABLE_NAME, ['id' => $order->expenses[0]->id, 'order_id' => $order->id]);
        $this->assertDatabaseMissing(Bonus::TABLE_NAME, ['id' => $order->bonuses[0]->id, 'order_id' => $order->id]);
        $this->assertDatabaseMissing(Vehicle::TABLE_NAME, ['id' => $order->vehicles[0]->id, 'order_id' => $order->id]);
        $this->assertDatabaseMissing(Inspection::TABLE_NAME, ['id' => $order->vehicles[0]->pickupInspection->id]);
        $this->assertDatabaseMissing(Inspection::TABLE_NAME, ['id' => $order->vehicles[0]->deliveryInspection->id]);
        $this->assertDatabaseMissing(Company::TABLE_NAME, ['id' => $company->id]);

        $this->assertDatabaseMissing(News::TABLE_NAME, ['id' => $news->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(DriverTripReport::TABLE_NAME, ['id' => $driverTripReport->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(Payroll::TABLE_NAME, ['id' => $payroll->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(QuestionAnswer::TABLE_NAME, ['id' => $questionAnswer->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(Contact::TABLE_NAME, ['id' => $contact->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(LibraryDocument::TABLE_NAME, ['id' => $libraryDocument->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(BonusType::TABLE_NAME, ['id' => $bonusType->id, 'carrier_id' => $company->id]);
        $this->assertDatabaseMissing(ExpenseType::TABLE_NAME, ['id' => $expenseType->id, 'carrier_id' => $company->id]);

        // check default company
        $this->assertDatabaseHas(Company::TABLE_NAME, ['id' => config('haulk.id')]);

        // check models
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $order->id]);
        $this->assertDatabaseHas(config('medialibrary.table_name'), ['model_id' => $order->vehicles[0]->pickupInspection->id]);

        // call delete models
        $this->artisan('media:delete-lost')
            ->assertExitCode(0);

        // call delete models again
        $this->artisan('media:delete-lost')
            ->assertExitCode(0);

        // check models
        $this->assertDatabaseMissing(config('medialibrary.table_name'), ['model_id' => $order->id]);
        $this->assertDatabaseMissing(config('medialibrary.table_name'), ['model_id' => $order->vehicles[0]->pickupInspection->id]);
    }

    private function createNews(int $carrierId): News
    {
        return factory(News::class)->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createDriverTripReport(int $carrierId): DriverTripReport
    {
        return DriverTripReport::factory()->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createPayroll(int $carrierId): Payroll
    {
        return Payroll::factory()->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createQuestionAnswer(int $carrierId): QuestionAnswer
    {
        return factory(QuestionAnswer::class)->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createContact(int $carrierId): Contact
    {
        return Contact::factory()->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createLibraryDocument(int $carrierId): LibraryDocument
    {
        return factory(LibraryDocument::class)->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createBonusType(int $carrierId): BonusType
    {
        return BonusType::factory()->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createExpenseType(int $carrierId): ExpenseType
    {
        return factory(ExpenseType::class)->create(
            [
                'carrier_id' => $carrierId,
            ]
        );
    }

    private function createOrder(Company $company): Order
    {
        $dispatcher = User::factory()->create(
            [
                'carrier_id' => $company->id,
            ]
        );
        $dispatcher->assignRole(User::DISPATCHER_ROLE);

        $driver = User::factory()->create(
            [
                'carrier_id' => $company->id,
                'owner_id' => $dispatcher->id,
            ]
        );
        $driver->assignRole(User::DRIVER_ROLE);

        $order = Order::factory()->create(
            [
                'carrier_id' => $company->id,
                'user_id' => $dispatcher->id,
                'dispatcher_id' => $dispatcher->id,
                'driver_id' => $driver->id,
            ]
        );

        Payment::factory()->create(
            [
                'order_id' => $order->id,
            ]
        );

        Expense::factory()->create(
            [
                'order_id' => $order->id,
            ]
        );

        Bonus::factory()->create(
            [
                'order_id' => $order->id,
            ]
        );

        $vehicle = Vehicle::factory()->create(
            [
                'order_id' => $order->id,
            ]
        );

        $pickupInspection = Inspection::factory()->create();
        $deliveryInspection = Inspection::factory()->create();

        $vehicle->pickup_inspection_id = $pickupInspection->id;
        $vehicle->delivery_inspection_id = $deliveryInspection->id;
        $vehicle->save();

        $this->loginAsCarrierDriver($driver);

        $this->postJson(
            route(
                'mobile.orders.vehicles.inspect-pickup-damage',
                [
                    $order->id,
                    $vehicle->id,
                ]
            ),
            [
                Order::INSPECTION_DAMAGE_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
            ]
        )
            ->assertOk();

        $superadmin = $company->getSuperAdmin();
        $this->loginAsCarrierSuperAdmin($superadmin);

        $this->postJson(
            route(
                'orders.attachments',
                $order->id
            ),
            [
                'attachment' => UploadedFile::fake()->createWithContent('info.txt', 'Some text for user file'),
            ]
        )
            ->assertOk();

        return $order;
    }
}
