<?php

namespace Tests\Feature\Api;

use App\Models\Contacts\Contact;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use App\Models\Users\User;
use App\Services\TimezoneService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Tests\ElasticsearchClear;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\Helpers\Traits\Orders\OrderESSavingHelper;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class MultiCompanyModelsTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;
    use OrderFactoryHelper;
    use WithFaker;
    use ElasticsearchClear;
    use OrderESSavingHelper;

    public function test_orders_visibility(): void
    {
        // carrier 1 orders and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $dispatcher1->assignRole(User::DISPATCHER_ROLE);

        $driver1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
                'owner_id' => $dispatcher1->id,
            ]
        );
        $driver1->assignRole(User::DRIVER_ROLE);

        $order1 = Order::factory()->make(
            [
                'carrier_id' => $carrier1->id,
                'user_id' => $dispatcher1->id,
                'dispatcher_id' => $dispatcher1->id,
                'driver_id' => $driver1->id,
            ]
        );

        $order1->setContactNameFields();
        $order1->setCalculatedStatusField();

        $order1->save();

        $vehicle1 = Vehicle::factory()->make(
            [
                'order_id' => $order1->id,
            ]
        );

        $vehicle1->save();

        // carrier 2 orders and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $dispatcher2->assignRole(User::DISPATCHER_ROLE);

        $driver2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
                'owner_id' => $dispatcher2->id,
            ]
        );
        $driver2->assignRole(User::DRIVER_ROLE);

        $order2 = Order::factory()->make(
            [
                'carrier_id' => $carrier2->id,
                'user_id' => $dispatcher2->id,
                'dispatcher_id' => $dispatcher2->id,
                'driver_id' => $driver2->id,
            ]
        );

        $order2->setContactNameFields();
        $order2->setCalculatedStatusField();

        $order2->save();

        $vehicle2 = Vehicle::factory()->make(
            [
                'order_id' => $order2->id,
            ]
        );

        $vehicle2->save();

        // check visibility 1 - 1 for dispatcher
        $this->loginAsCarrierDispatcher($dispatcher1);

        $this
            ->getJson(route('orders.show', $order1))
            ->assertOk();

        // check visibility 1 - 2 for dispatcher
        $this->getJson(route('orders.show', $order2))
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // check visibility 2 - 2 for dispatcher
        $this->loginAsCarrierDispatcher($dispatcher2);

        $this->getJson(route('orders.show', $order2))
            ->assertOk();

        // check visibility 2 - 1 for dispatcher
        $this->getJson(route('orders.show', $order1))
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // check vehicle visibility for driver
        $this->loginAsCarrierDriver($driver1);

        $this->getJson(route('order-mobile.show', $order1))
            ->assertOk();

        // own order own vehicle
        $this->getJson(
            route(
                'mobile.orders.get-vehicle',
                [
                    $order1,
                    $vehicle1
                ]
            )
        )
            ->assertOk();

        // own order wrong vehicle
        $this->getJson(
            route(
                'mobile.orders.get-vehicle',
                [
                    $order1,
                    $vehicle2
                ]
            )
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // wrong order own vehicle
        $this->getJson(
            route(
                'mobile.orders.get-vehicle',
                [
                    $order2,
                    $vehicle1
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_users_visibility(): void
    {
        // carrier 1 users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $dispatcher1->assignRole(User::DISPATCHER_ROLE);

        $driver1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
                'owner_id' => $dispatcher1->id,
            ]
        );
        $driver1->assignRole(User::DRIVER_ROLE);

        // carrier 2 users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $dispatcher2->assignRole(User::DISPATCHER_ROLE);

        $driver2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
                'owner_id' => $dispatcher2->id,
            ]
        );
        $driver2->assignRole(User::DRIVER_ROLE);

        // check dispatcher 1 sees driver 1
        $this->loginAsCarrierDispatcher($dispatcher1);

        $this->getJson(route('users.show', $driver1))
            ->assertOk();

        // check dispatcher 1 doesn't see driver 2
        $this->getJson(route('users.show', $driver2))
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // check dispatcher 2 sees driver 2
        $this->loginAsCarrierDispatcher($dispatcher2);

        $this->getJson(route('users.show', $driver2))
            ->assertOk();

        // check dispatcher 2 doesn't see driver 1
        $this->getJson(route('users.show', $driver1))
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_qna_visibility(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $admin1->assignRole(User::ADMIN_ROLE);

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $admin2->assignRole(User::ADMIN_ROLE);

        // check user 1 sees record 1
        $this->loginAsCarrierAdmin($admin1);

        $response = $this->postJson(
            route('question-answer.store'),
            [
                'question_en' => 'question_en',
                'answer_en' => 'answer_en',
            ]
        )
            ->assertCreated();

        $responseData = $response->json('data');

        $this->getJson(
            route(
                'question-answer.full',
                [
                    'questionAnswer' => $responseData['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 sees record 2
        $this->loginAsCarrierAdmin($admin2);

        $response = $this->postJson(
            route('question-answer.store'),
            [
                'question_en' => 'question_en',
                'answer_en' => 'answer_en',
            ]
        )
            ->assertCreated();

        $responseData2 = $response->json('data');

        $this->getJson(
            route(
                'question-answer.full',
                [
                    'questionAnswer' => $responseData2['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 doesn't see record 1
        $this->getJson(
            route(
                'question-answer.full',
                [
                    'questionAnswer' => $responseData['id'],
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_news_visibility(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $admin1->assignRole(User::ADMIN_ROLE);

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $admin2->assignRole(User::ADMIN_ROLE);

        // check user 1 sees record 1
        $this->loginAsCarrierAdmin($admin1);

        $response = $this->postJson(
            route('news.store'),
            [
                'title_en' => 'title_en',
                'body_en' => 'body_en',
            ]
        )
            ->assertCreated();

        $responseData = $response->json('data');

        $this->getJson(
            route(
                'news.show',
                [
                    'news' => $responseData['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 sees record 2
        $this->loginAsCarrierAdmin($admin2);

        $response = $this->postJson(
            route('news.store'),
            [
                'title_en' => 'title_en',
                'body_en' => 'body_en',
            ]
        )
            ->assertCreated();

        $responseData2 = $response->json('data');

        $this->getJson(
            route(
                'news.show',
                [
                    'news' => $responseData2['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 doesn't see record 1
        $this->getJson(
            route(
                'news.show',
                [
                    'news' => $responseData['id'],
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_library_visibility(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $admin1->assignRole(User::ADMIN_ROLE);

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $admin2->assignRole(User::ADMIN_ROLE);

        // check user 1 sees record 1
        $this->loginAsCarrierAdmin($admin1);

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()->image('image.jpg'),
            ]
        )
            ->assertCreated();

        $responseData = $response->json('data');

        $this->getJson(
            route(
                'library.show',
                [
                    'library' => $responseData['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 sees record 2
        $this->loginAsCarrierAdmin($admin2);

        $response = $this->postJson(
            route('library.store'),
            [
                'document' => UploadedFile::fake()->image('image2.jpg'),
            ]
        )
            ->assertCreated();

        $responseData2 = $response->json('data');

        $this->getJson(
            route(
                'library.show',
                [
                    'library' => $responseData2['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 doesn't see record 1
        $this->getJson(
            route(
                'library.show',
                [
                    'library' => $responseData['id'],
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_contacts_visibility(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $admin1->assignRole(User::ADMIN_ROLE);

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $admin2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $admin2->assignRole(User::ADMIN_ROLE);

        $state = factory(State::class)->create();

        // check user 1 sees record 1
        $this->loginAsCarrierAdmin($admin1);

        $response = $this->postJson(
            route('contacts.store'),
            [
                'full_name' => 'full_name',
                'address' => 'address',
                'city' => 'city',
                'state_id' => $state->id,
                'zip' => '12345',
                'type_id' => Contact::CONTACT_TYPE_PRIVATE,
                'phone' => '5555665555',
                'email' => $this->faker->email,
                'timezone' => resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->random()
            ]
        )
            ->assertCreated();

        $responseData = $response->json('data');

        $this->getJson(
            route(
                'contacts.show',
                [
                    'contact' => $responseData['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 sees record 2
        $this->loginAsCarrierAdmin($admin2);

        $response = $this->postJson(
            route('contacts.store'),
            [
                'full_name' => 'full_name',
                'address' => 'address',
                'city' => 'city',
                'state_id' => $state->id,
                'zip' => '12345',
                'type_id' => Contact::CONTACT_TYPE_PRIVATE,
                'phone' => '5555665555',
                'email' => $this->faker->email,
                'timezone' => resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->random()
            ]
        )
            ->assertCreated();

        $responseData2 = $response->json('data');

        $this->getJson(
            route(
                'contacts.show',
                [
                    'contact' => $responseData2['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 doesn't see record 1
        $this->getJson(
            route(
                'contacts.show',
                [
                    'contact' => $responseData['id'],
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_change_emails_visibility(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);
        $email1 = 'test1@mail.net';

        $admin1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $admin1->assignRole(User::ADMIN_ROLE);

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);
        $email2 = 'test2@mail.net';

        $admin2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $admin2->assignRole(User::ADMIN_ROLE);

        // create superadmin 1
        $superadmin = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $superadmin->assignRole(User::SUPERADMIN_ROLE);

        // check user 1 sees record 1
        $this->loginAsCarrierAdmin($admin1);

        $response = $this->postJson(
            route('change-email.store'),
            [
                'new_email' => $email1,
            ]
        )
            ->assertCreated();

        $responseData = $response->json('data');

        $this->assertDatabaseHas(
            'change_emails',
            [
                'new_email' => $email1,
                'carrier_id' => $carrier1->id,
            ]
        );

        // check user 2 sees record 2
        $this->loginAsCarrierAdmin($admin2);

        $response = $this->postJson(
            route('change-email.store'),
            [
                'new_email' => $email2,
            ]
        )
            ->assertCreated();

        $responseData2 = $response->json('data');

        $this->assertDatabaseHas(
            'change_emails',
            [
                'new_email' => $email2,
                'carrier_id' => $carrier2->id,
            ]
        );

        // check admin 1 sees record 1 and doesn't see record 2
        $this->loginAsCarrierAdmin($admin1);

        $this->deleteJson(
            route(
                'change-email.destroy',
                [
                    'change_email' => $responseData['id'],
                ]
            )
        )
            ->assertNoContent();

        $this->deleteJson(
            route(
                'change-email.destroy',
                [
                    'change_email' => $responseData2['id'],
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_order_comments_visibility(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $dispatcher1->assignRole(User::DISPATCHER_ROLE);

        $order1 = Order::factory()->make(
            [
                'carrier_id' => $carrier1->id,
                'user_id' => $dispatcher1->id,
                'dispatcher_id' => $dispatcher1->id,
            ]
        );

        $order1->setContactNameFields();
        $order1->setCalculatedStatusField();

        $order1->save();

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $dispatcher2->assignRole(User::DISPATCHER_ROLE);

        $order2 = Order::factory()->make(
            [
                'carrier_id' => $carrier2->id,
                'user_id' => $dispatcher2->id,
                'dispatcher_id' => $dispatcher2->id,
            ]
        );

        $order2->setContactNameFields();
        $order2->setCalculatedStatusField();

        $order2->save();

        // check user 1 adds and sees record 1
        $this->loginAsCarrierDispatcher($dispatcher1);

        $response = $this->postJson(
            route('order-comments.store', $order1),
            [
                'comment' => 'comment'
            ]
        )
            ->assertCreated();

        $responseData = $response->json('data');

        $this->getJson(
            route(
                'order-comments.show',
                [
                    'order' => $order1->id,
                    'comment' => $responseData['id'],
                ]
            )
        )
            ->assertOk();

        // check user 2 adds and sees record 2 and doesn't see record 1
        $this->loginAsCarrierDispatcher($dispatcher2);

        $response = $this->postJson(
            route('order-comments.store', $order2),
            [
                'comment' => 'comment 2'
            ]
        )
            ->assertCreated();

        $responseData2 = $response->json('data');

        $this->getJson(
            route(
                'order-comments.show',
                [
                    'order' => $order2->id,
                    'comment' => $responseData2['id'],
                ]
            )
        )
            ->assertOk();

        $this->getJson(
            route(
                'order-comments.show',
                [
                    'order' => $order2->id,
                    'comment' => $responseData['id'],
                ]
            )
        )
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_same_load_id(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $dispatcher1->assignRole(User::DISPATCHER_ROLE);

        $order1 = Order::factory()->make(
            [
                'carrier_id' => $carrier1->id,
                'user_id' => $dispatcher1->id,
                'dispatcher_id' => $dispatcher1->id,
            ]
        );

        $order1->setContactNameFields();
        $order1->setCalculatedStatusField();

        $order1->save();

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $dispatcher2->assignRole(User::DISPATCHER_ROLE);

        $order2 = Order::factory()->make(
            [
                'carrier_id' => $carrier2->id,
                'user_id' => $dispatcher2->id,
                'dispatcher_id' => $dispatcher2->id,
            ]
        );

        $order2->setContactNameFields();
        $order2->setCalculatedStatusField();

        $order2->save();

        // check user 1 adds and sees record 1
        $this->loginAsCarrierDispatcher($dispatcher1);
        $this->makeDocuments();

        $this->getJson(
            route(
                'orders.same-load-id',
                [
                    'load_id' => $order1->load_id,
                ]
            )
        )
            ->assertOk()
            ->assertJsonPath('data.0.load_id', $order1->load_id);

        $this->getJson(
            route(
                'orders.same-load-id',
                [
                    'load_id' => $order2->load_id,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_same_vin(): void
    {
        // carrier 1 records and users
        $carrier1 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher1 = User::factory()->create(
            [
                'carrier_id' => $carrier1->id,
            ]
        );
        $dispatcher1->assignRole(User::DISPATCHER_ROLE);

        $order1 = Order::factory()->make(
            [
                'carrier_id' => $carrier1->id,
                'user_id' => $dispatcher1->id,
                'dispatcher_id' => $dispatcher1->id,
            ]
        );

        $order1->setContactNameFields();
        $order1->setCalculatedStatusField();

        $order1->save();

        $vehicle = Vehicle::factory()->create(
            [
                'order_id' => $order1->id,
                'vin' => '1234567890abcdefg'
            ]
        );

        // carrier 2 records and users
        $carrier2 = $this->createCompany($this->getNewCompanyData(), config('pricing.plans.regular.slug'), true);

        $dispatcher2 = User::factory()->create(
            [
                'carrier_id' => $carrier2->id,
            ]
        );
        $dispatcher2->assignRole(User::DISPATCHER_ROLE);

        $order2 = Order::factory()->make(
            [
                'carrier_id' => $carrier2->id,
                'user_id' => $dispatcher2->id,
                'dispatcher_id' => $dispatcher2->id,
            ]
        );

        $order2->setContactNameFields();
        $order2->setCalculatedStatusField();

        $order2->save();

        $vehicle2 = Vehicle::factory()->create(
            [
                'order_id' => $order2->id,
                'vin' => '0987654321abcdefg'
            ]
        );

        // check user 1 adds and sees record 1
        $this->loginAsCarrierDispatcher($dispatcher1);
        $this->makeDocuments();
        $this->getJson(
            route(
                'orders.same-vin',
                [
                    'vin' => $vehicle->vin,
                ]
            )
        )
            ->assertOk()
            ->assertJsonPath('data.0.load_id', $order1->load_id);

        $this->getJson(
            route(
                'orders.same-vin',
                [
                    'vin' => $vehicle2->vin,
                ]
            )
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
