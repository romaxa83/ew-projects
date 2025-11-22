<?php

namespace Tests;

use App\Models\Admins\Admin;
use App\Models\Permissions\Role;
use App\Models\Saas\Company\Company;
use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use App\Models\Users\User;
use App\Repositories\Passport\PassportClientRepository;
use App\Repositories\Roles\RoleRepository;
use App\Repositories\Usdot\UsdotRepository;
use App\Services\Passport\UserPassportService;
use App\Services\Permissions\Templates\Accountant;
use App\Services\Permissions\Templates\BodyShopAdmin;
use App\Services\Permissions\Templates\BodyShopSuperAdmin;
use App\Services\Permissions\Templates\Dispatcher;
use App\Services\Permissions\Templates\Driver;
use App\Services\Saas\Companies\CompanyService;
use App\Services\Vehicles\VinDecodeService;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;
use Tests\Fake\Repositories\Usdot\UsdotFakeRepository;
use Tests\Fake\Services\Vehicles\FakeVinDecodeService;
use Tests\Helpers\Traits\AssertErrors;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithFaker;
    use AssertErrors;

    /**
     * @var User
     */
    protected $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(VinDecodeService::class, FakeVinDecodeService::class);
        $this->app->singleton(UsdotRepository::class, UsdotFakeRepository::class);

        $this->passportInit();

        RequestGuard::macro('logout', function () {
            /**@var RequestGuard $this */
            $this->user = null;
        });

        if (method_exists($this, 'clearElasticsearch')) {
            /**@see ElasticsearchClear::clearElasticsearch() */
            $this->clearElasticsearch();
        }
    }

    protected function passportInit(): void
    {
//        $this->artisan("passport:client --password --provider=admins --name='Admins'");
//        $this->artisan("passport:client --password --provider=users --name='Users'");

        $adminPassportClient = $this->getPassportRepository()->findForAdmin();
        Config::set('auth.oauth_client.admins.id', $adminPassportClient->id);
        Config::set('auth.oauth_client.admins.secret', $adminPassportClient->secret);

        $userPassportClient = $this->getPassportRepository()->findForUser();
        Config::set('auth.oauth_client.users.id', $userPassportClient->id);
        Config::set('auth.oauth_client.users.secret', $userPassportClient->secret);
    }

    protected function getPassportRepository(): PassportClientRepository
    {
        return resolve(PassportClientRepository::class);
    }

    protected function getAuthenticatedUser(): User
    {
        return $this->authenticatedUser;
    }

    protected function loginAsSaasAdmin(Admin $admin = null): Admin
    {
        $this->logout();

        if (!$admin) {
            $admin = factory(Admin::class)->create();
        }

        $this->authenticatedUser = Passport::actingAs($admin, [], Admin::GUARD);

        return $admin;
    }

    protected function loginAsSaasSuperAdmin(Admin $admin = null): Admin
    {
        $this->logout();

        if (!$admin) {
            $admin = factory(Admin::class)->create();
        }

        $role = Role::whereGuardName(Admin::GUARD)->whereName(User::SUPERADMIN_ROLE)->get();

        $admin->assignRole($role);

        $this->authenticatedUser = Passport::actingAs($admin, [], Admin::GUARD);

        return $admin;
    }

    protected function loginAsCarrierDispatcher(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(
                [
                    'carrier_id' => $this->getDefaultCarrierCompany()->id,
                ]
            );
            $user->assignRole(User::DISPATCHER_ROLE);
            $user->syncPermissions((new Dispatcher())->setPermissions());
        }

        $this->authenticatedUser = Passport::actingAs($user, [], User::GUARD);

        return $user;
    }

    protected function getDefaultCarrierCompany(): Company
    {
        $company = Company::firstOrCreate(
            [
                'id' => config('haulk.id'),
                'usdot' => config('haulk.usdot'),
                'email' => config('haulk.email'),
            ],
            config('haulk')
        );

        $company->active = true;

        $company->save();

        if (!$company->isSubscriptionActive()) {
            $company->createSubscription(config('pricing.plans.haulk-exclusive.slug'));
        }

        return $company;
    }

    protected function getNewCompanyData(): array
    {
        return [
            'email' => $this->faker->unique()->email,
            'password' => $this->faker->password(10),
            'usdot' => $this->faker->numberBetween(1000, 10000),
            'mc_number' => $this->faker->numberBetween(1000, 10000),
            'name' => $this->faker->company,
            'billing_email' => $this->faker->unique()->email,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'zip' => (string)$this->faker->randomNumber(),
            'phone' => $this->faker->e164PhoneNumber,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
        ];
    }

    protected function createCompany(array $companyRegistrationData, string $pricingPlanSlug, bool $withPaymentMethod = false): Company
    {
        $companyRegistration = new CompanyRegistration($companyRegistrationData);
        $companyRegistration->password = bcrypt($companyRegistrationData['password']);

        $company = new Company($companyRegistrationData);
        $company->active = true;
        $company->save();

        $company->createSuperAdmin($companyRegistration, $company->id);

        $company->createSubscription($pricingPlanSlug);

        $company->billingInfo()->create($companyRegistrationData);
        $company->insuranceInfo()->create($companyRegistrationData);
        $company->notificationSettings()->create($companyRegistrationData);

        resolve(CompanyService::class)->makeCompanySettingModel($company);

        if ($withPaymentMethod) {
            $paymentMethod = $company->paymentMethod()->create([]);
            $paymentMethod->payment_provider = 'test';
            $paymentMethod->payment_data = ['test'];
            $paymentMethod->save();
        }

        return $company;
    }

    protected function loginAsCarrierSuperAdmin(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            /** @var User $user */
            $user = User::factory()->create(
                [
                    'carrier_id' => $this->getDefaultCarrierCompany()->id,
                ]
            );
        }

        $user->assignRole(User::SUPERADMIN_ROLE);

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function loginAsCarrierAdmin(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            /** @var User $user */
            $user = User::factory()->create(
                [
                    'carrier_id' => $this->getDefaultCarrierCompany()->id,
                ]
            );
            $user->assignRole(User::ADMIN_ROLE);
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function loginAsCarrierAccountant(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(
                [
                    'carrier_id' => $this->getDefaultCarrierCompany()->id,
                ]
            );
            $user->assignRole(User::ACCOUNTANT_ROLE);
            $user->syncPermissions(resolve(Accountant::class)->setPermissions());
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function loginAsCarrierDriver(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(
                [
                    'carrier_id' => $this->getDefaultCarrierCompany()->id,
                ]
            );
            $user->assignRole(User::DRIVER_ROLE);
            $user->syncPermissions(resolve(Driver::class)->setPermissions());
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function loginAsBodyShopSuperAdmin(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(['carrier_id' => null]);
            $user->assignRole(User::BSSUPERADMIN_ROLE);
            $user->syncPermissions(resolve(BodyShopSuperAdmin::class)->setPermissions());
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function loginAsBodyShopAdmin(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(['carrier_id' => null]);
            $user->assignRole(User::BSADMIN_ROLE);
            $user->syncPermissions(resolve(BodyShopAdmin::class)->setPermissions());
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function loginAsBodyShopMechanic(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(['carrier_id' => null]);
            $user->assignRole(User::BSMECHANIC_ROLE);
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }

    protected function logout(): void
    {
        $guard = getGuard();
        if ($guard === null) {
            return;
        }

        \Auth::guard($guard)->logout();
    }

    /**
     * @return string[]
     * @throws BindingResolutionException
     * @throws Throwable
     */
    protected function getAuthGetParameters(): array
    {
        return [
            config('filebrowser.auth_token_parameter') => $this->getAccessToken(),
        ];
    }

    /**
     * @return string
     * @throws BindingResolutionException
     * @throws Throwable
     */
    protected function getAccessToken(): string
    {
        return $this->getAuth()['access_token'];
    }

    /**
     * @param null $email
     * @param null $password
     * @return array
     * @throws BindingResolutionException
     * @throws Throwable
     */
    protected function getAuth($email = null, $password = null): array
    {
        if (is_null($email)) {
            $email = 'some_email@example.com';
            $password = '123456789';
        }

        $attributes = [
            'email' => $email,
            'password' => $password,
        ];

        /** @var User $user */
        $user = User::factory()->create($attributes + ['status' => User::STATUS_ACTIVE]);
        $user->assignRole(User::SUPERADMIN_ROLE);

        $passport = $this->app->make(UserPassportService::class);

        return $passport->auth($email, $password);
    }

    protected function getRoleRepository(): RoleRepository
    {
        return resolve(RoleRepository::class);
    }

    protected function isStorageS3(): bool
    {
        return config('filesystems.default') === 's3';
    }

    protected function loginAsCarrierOwner(User $user = null): User
    {
        $this->logout();

        if (!$user) {
            $user = User::factory()->create(
                [
                    'carrier_id' => $this->getDefaultCarrierCompany()->id,
                ]
            );
            $user->assignRole(User::OWNER_ROLE);
        }

        $this->authenticatedUser = Passport::actingAs($user, ['*'], User::GUARD);

        return $user;
    }
}
