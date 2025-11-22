<?php

namespace App\Models\Orders\Traits;

use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\OrderComment;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use App\Models\Orders\Vehicle;
use App\Models\Payrolls\Payroll;
use App\Models\Permissions\Role;
use App\Models\Tags\Tag;
use App\Models\Users\AuthHistory;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Order
 *
 * @see self::scopeLoadManyMobile()
 * @method static static|Builder loadManyMobile()
 *
 * @see self::scopeLoadMany()
 * @method static static|Builder loadMany()
 */
trait OrderLoadScopesTrait
{
    public static function getManyRelations(bool $mobile = false): array
    {
        $with = [
            'user' => static fn(Relation $relation) => self::getUserMiniRelation($relation),
            'driver' => static fn(Relation $relation) => self::getUserMiniRelation($relation),
            'dispatcher' => static fn(Relation $relation) => self::getUserMiniRelation($relation),
            'vehicles' => static fn(Relation $relation) => self::getVehiclesRelation($relation, $mobile),
            'payment' => static fn(Relation $relation) => self::getPaymentRelation($relation, false),
            'paymentStages' => static fn(Relation $relation) => $relation
                ->whereIn(
                    "id",
                    PaymentStage::selectRaw('MAX(id) AS id')
                        ->whereRaw(preg_replace("/.+ where /", "", $relation->toSql()))
                        ->setBindings($relation->getBindings())
                        ->groupBy('order_id')
                ),
            'expenses' => static fn(Relation $relation) => self::getExpensesRelation($relation, false),
            'bonuses' => static fn(Relation $relation) => self::getBonusesRelation($relation),
            'comments' => static fn(Relation $relation) => !$mobile ? $relation
                ->joinSub(
                    OrderComment::selectRaw("MAX(id) as id, COUNT(*) as count")
                        ->whereRaw(preg_replace("/.+ where /", "", $relation->toSql()))
                        ->setBindings($relation->getBindings())
                        ->groupBy('order_id'),
                    OrderComment::TABLE_NAME . '_sub',
                    OrderComment::TABLE_NAME . '.id',
                    '=',
                    OrderComment::TABLE_NAME . '_sub.id'
                )
                ->select(
                    [
                        OrderComment::TABLE_NAME . '.id',
                        OrderComment::TABLE_NAME . '.order_id',
                        OrderComment::TABLE_NAME . '.comment',
                        OrderComment::TABLE_NAME . '_sub.count',
                    ]
                ) : $relation
                ->select(
                    [
                        OrderComment::TABLE_NAME . '.id',
                        OrderComment::TABLE_NAME . '.order_id',
                        OrderComment::TABLE_NAME . '.user_id',
                        OrderComment::TABLE_NAME . '.role_id',
                        OrderComment::TABLE_NAME . '.comment',
                        OrderComment::TABLE_NAME . '.timezone',
                        OrderComment::TABLE_NAME . '.created_at',
                    ]
                )
                ->where(OrderComment::TABLE_NAME . '.role_id', Role::findByName(User::DRIVER_ROLE)->id)
                ->with(
                    [
                        'user' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    User::TABLE_NAME . '.id',
                                    User::TABLE_NAME . '.first_name',
                                    User::TABLE_NAME . '.last_name'
                                ]
                            )
                            ->with('media')
                    ]
                ),
            'payrolls' => static fn(Relation $relation) => $relation
                ->whereIn(
                    Payroll::TABLE_NAME . ".id",
                    DB::table('order_payroll')
                        ->selectRaw('MIN(payroll_id) AS id')
                        ->whereRaw(preg_replace("/.+ where /", "", $relation->toSql()))
                        ->setBindings($relation->getBindings())
                        ->groupBy('order_id')
                )
                ->select(
                    [
                        Payroll::TABLE_NAME . '.id',
                        Payroll::TABLE_NAME . '.start',
                        Payroll::TABLE_NAME . '.end',
                        Payroll::TABLE_NAME . '.is_paid'
                    ]
                ),
            'tags' => static fn(Relation $relation) => $relation
                ->select(
                    [
                        Tag::TABLE_NAME . '.id',
                        Tag::TABLE_NAME . '.name',
                        Tag::TABLE_NAME . '.color',
                    ]
                ),
        ];
        if ($mobile) {
            $with[] = 'media';
        }
        return $with;
    }

    private static function getUserMiniRelation(Relation $relation): Relation
    {
        return $relation
            ->select(
                [
                    User::TABLE_NAME . '.id',
                    User::TABLE_NAME . '.first_name',
                    User::TABLE_NAME . '.last_name'
                ]
            )
            ->with(
                [
                    'roles' => static fn(Relation $relation) => $relation
                        ->select(Role::TABLE . ".id"),
                ]
            );
    }

    public static function getVehiclesRelation(Relation $relation, bool $withInspections = true): Relation
    {
        $relation = $relation
            ->select(
                [
                    Vehicle::TABLE_NAME . '.id',
                    Vehicle::TABLE_NAME . '.order_id',
                    Vehicle::TABLE_NAME . '.inop',
                    Vehicle::TABLE_NAME . '.enclosed',
                    Vehicle::TABLE_NAME . '.vin',
                    Vehicle::TABLE_NAME . '.year',
                    Vehicle::TABLE_NAME . '.make',
                    Vehicle::TABLE_NAME . '.model',
                    Vehicle::TABLE_NAME . '.type_id',
                    Vehicle::TABLE_NAME . '.color',
                    Vehicle::TABLE_NAME . '.license_plate',
                    Vehicle::TABLE_NAME . '.odometer',
                    Vehicle::TABLE_NAME . '.stock_number',
                    Vehicle::TABLE_NAME . '.pickup_inspection_id',
                    Vehicle::TABLE_NAME . '.delivery_inspection_id'
                ]
            );
        if (!$withInspections) {
            return $relation;
        }
        return $relation->with(
            [
                'pickupInspection' => static fn(Relation $relation) => self::getInspectionRelation($relation),
                'deliveryInspection' => static fn(Relation $relation) => self::getInspectionRelation($relation),
            ]
        );
    }

    public static function getInspectionRelation(Relation $relation): Relation
    {
        return $relation
            ->select(
                [
                    Inspection::TABLE_NAME . '.id',
                    Inspection::TABLE_NAME . '.vin',
                    Inspection::TABLE_NAME . '.condition_dark',
                    Inspection::TABLE_NAME . '.condition_snow',
                    Inspection::TABLE_NAME . '.condition_rain',
                    Inspection::TABLE_NAME . '.condition_dirty_vehicle',
                    Inspection::TABLE_NAME . '.odometer',
                    Inspection::TABLE_NAME . '.notes',
                    Inspection::TABLE_NAME . '.num_keys',
                    Inspection::TABLE_NAME . '.num_remotes',
                    Inspection::TABLE_NAME . '.num_headrests',
                    Inspection::TABLE_NAME . '.drivable',
                    Inspection::TABLE_NAME . '.windscreen',
                    Inspection::TABLE_NAME . '.glass_all_intact',
                    Inspection::TABLE_NAME . '.title',
                    Inspection::TABLE_NAME . '.cargo_cover',
                    Inspection::TABLE_NAME . '.spare_tire',
                    Inspection::TABLE_NAME . '.radio',
                    Inspection::TABLE_NAME . '.manuals',
                    Inspection::TABLE_NAME . '.navigation_disk',
                    Inspection::TABLE_NAME . '.plugin_charger_cable',
                    Inspection::TABLE_NAME . '.headphones',
                    Inspection::TABLE_NAME . '.has_vin_inspection',
                ]
            )
            ->with('media');
    }

    private static function getPaymentRelation(Relation $relation, bool $withMedia = true): Relation
    {
        $relation = $relation
            ->select(
                [
                    Payment::TABLE_NAME . '.id',
                    Payment::TABLE_NAME . '.order_id',
                    Payment::TABLE_NAME . '.terms',
                    Payment::TABLE_NAME . '.invoice_id',
                    Payment::TABLE_NAME . '.invoice_notes',
                    Payment::TABLE_NAME . '.invoice_issue_date',
                    Payment::TABLE_NAME . '.total_carrier_amount',
                    Payment::TABLE_NAME . '.broker_fee_planned_date',
                    Payment::TABLE_NAME . '.customer_payment_amount',
                    Payment::TABLE_NAME . '.customer_payment_method_id',
                    Payment::TABLE_NAME . '.customer_payment_location',
                    Payment::TABLE_NAME . '.customer_payment_invoice_id',
                    Payment::TABLE_NAME . '.customer_payment_invoice_issue_date',
                    Payment::TABLE_NAME . '.customer_payment_invoice_notes',
                    Payment::TABLE_NAME . '.broker_payment_amount',
                    Payment::TABLE_NAME . '.broker_payment_method_id',
                    Payment::TABLE_NAME . '.broker_payment_days',
                    Payment::TABLE_NAME . '.broker_payment_begins',
                    Payment::TABLE_NAME . '.broker_payment_invoice_id',
                    Payment::TABLE_NAME . '.broker_payment_invoice_issue_date',
                    Payment::TABLE_NAME . '.broker_payment_invoice_notes',
                    Payment::TABLE_NAME . '.broker_fee_amount',
                    Payment::TABLE_NAME . '.broker_fee_method_id',
                    Payment::TABLE_NAME . '.broker_fee_days',
                    Payment::TABLE_NAME . '.broker_fee_begins',
                    Payment::TABLE_NAME . '.driver_payment_data_sent',
                    Payment::TABLE_NAME . '.driver_payment_amount',
                    Payment::TABLE_NAME . '.driver_payment_uship_code',
                    Payment::TABLE_NAME . '.driver_payment_comment',
                    Payment::TABLE_NAME . '.driver_payment_method_id',
                    Payment::TABLE_NAME . '.driver_payment_timestamp',
                    Payment::TABLE_NAME . '.driver_payment_account_type'
                ]
            );
        if (!$withMedia) {
            return $relation;
        }
        return $relation->with('media');
    }

    private static function getExpensesRelation(Relation $relation, bool $withMedia = true): Relation
    {
        $relation = $relation
            ->select(
                [
                    Expense::TABLE_NAME . '.id',
                    Expense::TABLE_NAME . '.order_id',
                    Expense::TABLE_NAME . '.type_id',
                    Expense::TABLE_NAME . '.price',
                    Expense::TABLE_NAME . '.date',
                    Expense::TABLE_NAME . '.to'
                ]
            );
        if (!$withMedia) {
            return $relation;
        }
        return $relation->with('media');
    }

    private static function getBonusesRelation(Relation $relation): Relation
    {
        return $relation
            ->select(
                [
                    Bonus::TABLE_NAME . '.id',
                    Bonus::TABLE_NAME . '.order_id',
                    Bonus::TABLE_NAME . '.type',
                    Bonus::TABLE_NAME . '.price',
                    Bonus::TABLE_NAME . '.to'
                ]
            );
    }

    public static function getSingleRelations(): array
    {
        return [
            'user' => static fn(Relation $relation) => self::getUserMiniRelation($relation),
            'driver' => static fn(Relation $relation) => $relation
                ->select(
                    [
                        User::TABLE_NAME . '.id',
                        User::TABLE_NAME . '.first_name',
                        User::TABLE_NAME . '.last_name',
                        User::TABLE_NAME . '.email',
                        User::TABLE_NAME . '.phone',
                        User::TABLE_NAME . '.phone_extension',
                        User::TABLE_NAME . '.phones',
                        User::TABLE_NAME . '.status',
                        User::TABLE_NAME . '.deleted_at'
                    ]
                )
                ->with(
                    [
                        'roles' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    Role::TABLE . '.id',
                                    Role::TABLE . '.name'
                                ]
                            )
                            ->limit(1),
                        'media',
                        'lastLogin' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    AuthHistory::TABLE . '.user_id',
                                    AuthHistory::TABLE . '.created_at'
                                ]
                            ),
                    ]
                ),
            'dispatcher' => static fn(Relation $relation) => $relation
                ->select(
                    [
                        User::TABLE_NAME . '.id',
                        User::TABLE_NAME . '.first_name',
                        User::TABLE_NAME . '.last_name',
                        User::TABLE_NAME . '.email',
                        User::TABLE_NAME . '.phone',
                        User::TABLE_NAME . '.phone_extension',
                        User::TABLE_NAME . '.phones',
                        User::TABLE_NAME . '.status',
                        User::TABLE_NAME . '.deleted_at'
                    ]
                )
                ->with(
                    [
                        'roles' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    Role::TABLE . '.id',
                                    Role::TABLE . '.name'
                                ]
                            )
                            ->limit(1),
                        'media',
                        'drivers' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    User::TABLE_NAME . '.id',
                                    User::TABLE_NAME . '.owner_id',
                                    User::TABLE_NAME . '.first_name',
                                    User::TABLE_NAME . '.last_name',
                                    User::TABLE_NAME . '.status'
                                ]
                            ),
                        'lastLogin' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    AuthHistory::TABLE . '.user_id',
                                    AuthHistory::TABLE . '.created_at'
                                ]
                            ),
                    ]
                ),
            'vehicles' => static fn(Relation $relation) => self::getVehiclesRelation($relation, true),
            'payment' => static fn(Relation $relation) => self::getPaymentRelation($relation),
            'paymentStages',
            'expenses' => static fn(Relation $relation) => self::getExpensesRelation($relation),
            'bonuses' => static fn(Relation $relation) => self::getBonusesRelation($relation),
            'media',
            'comments' => static fn(Relation $relation) => $relation
                ->select(
                    [
                        OrderComment::TABLE_NAME . '.id',
                        OrderComment::TABLE_NAME . '.created_at',
                        OrderComment::TABLE_NAME . '.order_id',
                        OrderComment::TABLE_NAME . '.comment',
                        OrderComment::TABLE_NAME . '.user_id',
                        OrderComment::TABLE_NAME . '.timezone'
                    ]
                )
                ->where(OrderComment::TABLE_NAME . '.role_id', Role::findByName(User::DRIVER_ROLE)->id)
                ->with(
                    [
                        'user' => static fn(Relation $relation) => $relation
                            ->select(
                                [
                                    User::TABLE_NAME . '.id',
                                    User::TABLE_NAME . '.first_name',
                                    User::TABLE_NAME . '.last_name'
                                ]
                            )
                            ->with(
                                [
                                    'roles' => static fn(Relation $relation) => $relation
                                        ->select(Role::TABLE . '.id')
                                        ->limit(1),
                                    'media'
                                ]
                            )
                    ]
                ),
            'payrolls' => static fn(Relation $relation) => $relation
                ->select(
                    [
                        Payroll::TABLE_NAME . '.id',
                        Payroll::TABLE_NAME . '.start',
                        Payroll::TABLE_NAME . '.end',
                        Payroll::TABLE_NAME . '.is_paid'
                    ]
                )
                ->limit(1),
            'tags' => static fn(Relation $relation) => $relation
                ->select(
                    [
                        Tag::TABLE_NAME . '.id',
                        Tag::TABLE_NAME . '.name',
                        Tag::TABLE_NAME . '.color',
                    ]
                ),
        ];
    }

    public function scopeLoadMany(Builder $builder): void
    {
        $builder->with(self::getManyRelations());
    }

    public function scopeLoadManyMobile(Builder $builder): void
    {
        $builder->with(self::getManyRelations(true));
    }

    /**
     * @return Order|OrderLoadScopesTrait.\App\Models\Orders\Traits\OrderLoadScopesTrait.loadMissing
     */
    public function loadMissingRelations()
    {
        return $this->load(self::getSingleRelations());
    }
}
