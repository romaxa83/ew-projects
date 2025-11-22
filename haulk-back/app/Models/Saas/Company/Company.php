<?php

namespace App\Models\Saas\Company;

use App\Dto\Payments\PaymentDataAbstract;
use App\Entities\Settings\Contact;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Http\Controllers\Api\Helpers\DbConnections;
use App\ModelFilters\Saas\Company\CompanyFilter;
use App\Models\Billing\Invoice;
use App\Models\Files\HasMedia;
use App\Models\Files\SettingImage;
use App\Models\Files\Traits\HasMediaTrait;
use App\Models\Locations\State;
use App\Models\Orders\Order;
use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DevicePayment;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Models\Saas\Pricing\CompanySubscription;
use App\Models\Saas\Pricing\PricingPlan;
use App\Models\Users\User;
use App\Traits\Filterable;
use Database\Factories\Saas\Company\CompanyFactory;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Models\Saas\Company\Company
 *
 * @property int $id
 * @property bool $active
 * @property string $usdot
 * @property string|null $ga_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $address
 * @property string|null $city
 * @property int|null $state_id
 * @property string|null $zip
 * @property string|null $timezone
 * @property string|null $phone
 * @property string|null $phone_name
 * @property array driver_salary_contact_info
 * @property mixed|null $phones
 * @property string $email
 * @property string|null $fax
 * @property string|null $website
 * @property bool $use_in_body_shop
 * @property float|null $speed_limit
 * @property bool send_to_sendpulse             // отправлены ли данные в сервис sendpulse
 * @property int not_payment_card_count         // кол-во отправленных писем, если не добавлена кредитная карта
 * @property int not_login_free_trial_count     // кол-во отправленных писем, если добавлена кредитная карта но пользователь не заходит в систему
 * @property bool send_before_trial             // отправлено ли письмо за день до конца пробного периода
 * @property Carbon|null registration_at
 * @property-read CompanyNotificationSettings|null $notificationSettings
 * @property-read Device[]|Collection $gpsDevices
 * @method static Builder|static filter(array $input = [], $filter = null)
 * @method static Builder|static newModelQuery()
 * @method static Builder|static newQuery()
 * @method static Builder|static paginateFilter($perPage = null, $columns = [], $pageName = 'page', $page = null)
 * @method static Builder|static query()
 * @method static Builder|static simplePaginateFilter(?int $perPage = null, ?int $columns = [], ?int $pageName = 'page', ?int $page = null)
 * @method static Builder|static whereActive($value)
 * @method static Builder|static whereAddress($value)
 * @method static Builder|static whereBeginsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|static whereCity($value)
 * @method static Builder|static whereCreatedAt($value)
 * @method static Builder|static whereEmail($value)
 * @method static Builder|static whereEndsWith(string $column, string $value, string $boolean = 'and')
 * @method static Builder|static whereFax($value)
 * @method static Builder|static whereId($value)
 * @method static Builder|static whereLike(string $column, string $value, string $boolean = 'and')
 * @method static Builder|static whereName($value)
 * @method static Builder|static wherePhone($value)
 * @method static Builder|static wherePhoneName($value)
 * @method static Builder|static wherePhones($value)
 * @method static Builder|static whereStateId($value)
 * @method static Builder|static whereTimezone($value)
 * @method static Builder|static whereUpdatedAt($value)
 * @method static Builder|static whereUsdot($value)
 * @method static Builder|static whereWebsite($value)
 * @method static Builder|static whereZip($value)
 * @mixin Eloquent
 *
 * @see static::gpsDeviceSubscription()
 * @property DeviceSubscription|null gpsDeviceSubscription
 *
 * @see static::gpsDevicePaymentItems()
 * @property DevicePayment|Collection gpsDevicePaymentItems
 *
 * @see static::subscription()
 * @property CompanySubscription|null subscription
 *
 * @method static CompanyFactory factory(...$parameters)
 */
class Company extends Model implements HasMedia
{
    use Filterable;
    use Notifiable;
    use HasMediaTrait;
    use HasFactory;

    public const TABLE_NAME = 'companies';

    protected $table = self::TABLE_NAME;

    protected $connection = DbConnections::DEFAULT;

    public const LOGO_FIELD_CARRIER = 'logo';
    public const W9_FIELD_CARRIER = 'w9_form_image';
    public const USDOT_FIELD_CARRIER = 'usdot_number_image';

    protected $fillable = [
        'active',
        'ga_id',
        'usdot',
        'mc_number',
        'name',
        'address',
        'city',
        'state_id',
        'zip',
        'timezone',
        'phone',
        'phone_name',
        'phone_extension',
        'phones',
        'email',
        'fax',
        'website',
        'status',
        'saas_confirm_token',
        'saas_decline_token',
        'saas_date_token_create',
        'saas_date_delete',
        'crm_confirm_token',
        'crm_decline_token',
        'crm_date_token_create',
        'use_in_body_shop',
        'speed_limit',
        'driver_salary_contact_info',
        'send_to_sendpulse',
        'registration_at',
        'not_payment_card_count',
        'not_login_free_trial_count',
        'send_before_trial',
    ];

    protected $casts = [
        'active' => 'boolean',
        'send_to_sendpulse' => 'boolean',
        'send_before_trial' => 'boolean',
        'phones' => 'array',
        'registration_at' => 'datetime',
        'use_in_body_shop' => 'boolean',
        'speed_limit' => 'double',
        'driver_salary_contact_info' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'carrier_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'carrier_id');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(CompanySubscription::class);
    }

    public function gpsDeviceSubscription(): HasOne
    {
        return $this->hasOne(DeviceSubscription::class);
    }

    public function gpsDevicePaymentItems(): HasMany
    {
        return $this->hasMany(DevicePayment::class);
    }

    public function hasGpsDeviceSubscription(): bool
    {
        return $this->gpsDeviceSubscription !== null;
    }

    public function setting(): HasOne
    {
        return $this->hasOne(CompanySetting::class);
    }

    public function billingInfo(): HasOne
    {
        return $this->hasOne(CompanyBillingInfo::class);
    }

    public function insuranceInfo(): HasOne
    {
        return $this->hasOne(CompanyInsuranceInfo::class);
    }

    public function notificationSettings(): HasOne
    {
        return $this->hasOne(CompanyNotificationSettings::class);
    }

    public function paymentMethod(): HasOne
    {
        return $this->hasOne(CompanyPaymentMethod::class);
    }

    public function paymentContact(): HasOne
    {
        return $this->hasOne(CompanyPaymentContact::class);
    }

    public function modelFilter(): string
    {
        return CompanyFilter::class;
    }

    public function getImageClass(): string
    {
        return SettingImage::class;
    }

    public function isInTrialPeriod(): bool
    {
        return $this->subscription ? $this->subscription->isInTrialPeriod() : false;
    }

    public function isExclusivePlan(): bool
    {
        return $this->subscription
            ? $this->subscription->pricingPlan->isExclusive()
            : false;
    }

    public function isFreePlan(): bool
    {
        return $this->subscription
            ? $this->subscription->pricingPlan->isFree()
            : false;
    }

    public function isTrialExpired(): bool
    {
        return $this->subscription ? $this->subscription->isTrialExpired() : false;
    }

    public function isSubscriptionActive(): bool
    {
        return $this->subscription ? $this->subscription->isActive() : false;
    }

    public function hasSubscription(): bool
    {
        return $this->subscription !== null;
    }

    public function hasPaymentMethod(): bool
    {
        return $this->paymentMethod !== null && $this->paymentMethod->hasPaymentData();
    }

    public function hasUnpaidInvoices(): bool
    {
        return $this->invoices->where('is_paid', false)->where('pending', false)->count() > 0;
    }

    public function lastPaymentAttemptFailed(): bool
    {
        $invoices = $this->invoices->where('is_paid', false)->where('pending', false);

        if ($invoices) {
            foreach ($invoices as $invoice) {
                if ($invoice->attempt > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    public function paymentAttemptsCountExhausted(): bool
    {
        $invoices = $this->invoices->where('is_paid', false)->where('pending', false);

        if ($invoices) {
            foreach ($invoices as $invoice) {
                /** @var $invoice Invoice*/
                if ($invoice->chargeAttemptsExhausted()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function prePaymentAttemptsCountExhausted(): bool
    {
        $invoices = $this->invoices->where('is_paid', false)->where('pending', false);

        if ($invoices) {
            foreach ($invoices as $invoice) {
                /** @var $invoice Invoice*/
                if ($invoice->preChargeAttemptsExhausted()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasPaymentContact(): bool
    {
        return $this->paymentContact !== null;
    }

    public function getPaymentContactData(): array
    {
        if ($this->hasPaymentContact()) {
            if ($this->paymentContact->useAccountingContact()) {
                return [
                    'full_name' => '',
                    'email' => $this->billingInfo->billing_email,
                ];
            }

            return [
                'full_name' => $this->paymentContact->full_name,
                'email' => $this->paymentContact->email,
            ];
        }

        $superadmin = $this->getSuperAdmin();

        return [
            'full_name' => $superadmin->full_name,
            'email' => $superadmin->email,
        ];
    }

    public function getPaymentMethodData(): ?PaymentDataAbstract
    {
        return $this->paymentMethod && $this->paymentMethod->hasPaymentData()
            ? $this->paymentMethod->getPaymentData()
            : null;
    }

    public function getSuperAdmin(): ?User
    {
        return $this->users()
            ->onlySuperadmins()
            ->first();
    }

    public function getCompanyId(): int
    {
        return $this->id;
    }

    public function getCompanyName(): string
    {
        return $this->name;
    }

    public function getContactEmail(): string
    {
        return $this->email;
    }

    public function getContactPhone(): ?string
    {
        return $this->phone;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getBillingEmail(): ?string
    {
        return $this->billingInfo->billing_email;
    }

    public function getTermsAndConditions(): ?string
    {
        return $this->billingInfo->billing_terms;
    }

    public function getBillingPaymentDetails(): ?string
    {
        return $this->billingInfo->billing_payment_details;
    }

    public function ifAddPickupDeliveryDatesToBol(): bool
    {
        return $this->notificationSettings->add_pickup_delivery_dates_to_bol;
    }

    public function getBillingContactsAsString(): ?string
    {
        if (empty($contacts = $this->getBillingContacts())) {
            return null;
        }

        return implode(', ', $contacts);
    }

    public function getBillingContacts(): array
    {
        $result = [];

        if ($this->billingInfo->billing_phones) {
            foreach ($this->billingInfo->billing_phones as $phone) {
                $result[] = new Contact($phone);
            }
        }

        return $result;
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function isDeleted(): bool
    {
        return !is_null($this->saas_date_delete);
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->save();
    }

    public function toggleActivity(): bool
    {
        try {
            if (array_key_exists('active', $this->getAttributes())) {
                if ($this->active === true) {
                    $this->active = false;
                } else {
                    $this->active = true;
                    $this->saas_confirm_token = null;
                    $this->saas_decline_token = null;
                    $this->saas_date_token_create = null;
                    $this->saas_date_delete = null;
                }
                $this->save();
                return true;
            }
            return false;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    public function createSuperAdmin(CompanyRegistration $companyRegistration, ?int $carrier_id = null, ?int $broker_id = null): void
    {
        $user = new User();

        $user->status = User::STATUS_ACTIVE;
        $user->carrier_id = $carrier_id;
        $user->broker_id = $broker_id;
        $user->first_name = $companyRegistration->first_name;
        $user->last_name = $companyRegistration->last_name;
        $user->email = $companyRegistration->email;
        $user->phone = $companyRegistration->phone;
        $user->setPasswordHash($companyRegistration->password);

        $user->save();

        $user->assignRole(User::SUPERADMIN_ROLE);
    }

    public function createSubscription(string $planSlug): void
    {
        $plan = PricingPlan::where('slug', $planSlug)->first();

        if (!$plan) {
            throw new Exception(trans('Pricing plan not found.'));
        }

        $subscription = new CompanySubscription();

        $subscription->pricing_plan_id = $plan->id;
        $subscription->company_id = $this->id;
        $subscription->is_trial = $plan->isTrial();
        $subscription->billing_start = now()->startOfDay();
        $subscription->billing_end = now()->add($plan->getDuration())->endOfDay();

        $subscription->save();
    }

    public function renewSubscription(): void
    {
        $subscription = $this->subscription;
        $plan = $subscription->pricingPlan;

        $subscription->canceled = false;

        if (!$plan->isTrial()) {
            $subscription->is_trial = false;
            $subscription->billing_start = now()->startOfDay();
            $subscription->billing_end = now()->add($plan->getDuration())->endOfDay();
        }

        $subscription->save();
    }

    private function normalizePhones(array $billingPhones): string
    {
        $result = [];

        foreach ($billingPhones as $phone) {
            if (isset($phone['number']) && ($formatted = phone_format($phone['number']))) {
                $result[] = $formatted;
            }
        }

        return implode(', ', $result);
    }

    public function getProfileData(): object
    {
        $profileData = $this->toArray() + $this->billingInfo->toArray();

        if (isset($profileData['state_id'])) {
            $profileData['state'] = State::find($profileData['state_id']);
        }

        if (isset($profileData['contact_phones'])) {
            $profileData['contact_phones'] = $this->normalizePhones($profileData['contact_phones']);
        }

        if (isset($profileData['billing_phone'])) {
            $profileData['billing_phone'] = phone_format($profileData['billing_phone']);
        }

        if (isset($profileData['billing_phones'])) {
            $profileData['billing_phones'] = $this->normalizePhones($profileData['billing_phones']);
        }

        if (isset($profileData['city'])) {
            $cityArr = explode(',', $profileData['city']);
            $profileData['city'] = $cityArr[0];
        }

        return (object) $profileData;
    }

    public function getMailContactString(): string
    {
        return "contact {$this->getContactPhone()} or email us at {$this->getContactEmail()}";
    }

    public function getEmployeesCount(): int
    {
        // TODO: add other types
        return User::where('carrier_id', $this->id)->count();
    }

    public function getOrdersCount(): int
    {
        // TODO: add other types
        return Order::where('carrier_id', $this->id)->count();
    }

    public function getCompanyStatus(): ?string
    {
        return self::getCompanyStatusByCode($this->status);
    }

    public static function getCompanyStatusByCode(?string $code): ?string
    {
        if (!$code) {
            return null;
        }

        switch ($code) {
            case 'A':
                return 'Authorized';
            case 'N':
                return 'Not authorized';
        }

        return $code;
    }

    public function getFileBrowserPrefix(): ?string
    {
        if ($this->setting) {
            return $this->setting->getFileBrowserPrefix();
        }

        return null;
    }

    public function gpsDevices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function countGpsDevices(): int
    {
        return $this->gpsDevices()->count();
    }

    public function countActiveGpsDevices(): int
    {
        return $this->gpsDevices->where('status', DeviceStatus::ACTIVE)->count();
    }

    public function isGPSEnabled(): bool
    {
        return $this->gpsDeviceSubscription
            && (
                $this->gpsDeviceSubscription->status->isActive()
                || $this->gpsDeviceSubscription->status->isActiveTill()
            )
            ;
    }

    public function getTimezoneOrDefault(): string
    {
        return $this->getTimezone()
            ? $this->getTimezone()
            : config('app.client_timezone_default')
            ;
    }
}
