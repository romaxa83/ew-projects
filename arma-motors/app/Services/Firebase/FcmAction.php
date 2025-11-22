<?php

namespace App\Services\Firebase;

use App\Models\Agreement\Agreement;
use App\Models\Catalogs\Service\Service;
use App\Models\Notification\Fcm;
use App\Models\Recommendation\Recommendation;
use App\Models\User\User;

final class FcmAction
{
    public const MODEL_ORDER = 'order';
    public const MODEL_RECOMMENDATION = 'recommendation';
    public const MODEL_AGREEMENT = 'agreement';
    public const ACCEPT_AGREEMENT = 'accept_agreement';

    public const ACTION_TEST = 'test';
    public const SERVICE_PAYMENT = 'service_payment';
    public const CAR_MODERATE = 'car_moderate';
    public const CAN_ADD_CAR_TO_GARAGE = 'can_add_car_to_garage';
    public const EMAIL_VERIFY = 'email_verify';
    public const ORDER_ACCEPT = 'order_accept';

    public const ORDER_COMPLETE = 'order_complete';
    public const ORDER_REMIND = 'order_remind';
    public const RECOMMEND_SERVICE = 'recommend_service';
    public const RECONCILIATION_WORK = 'reconciliation_work';
    public const PROMOTIONS = 'promotions';
    public const DISCOUNTS = 'discounts';
    public const EDIT_PHONE_SUCCESS = 'edit_phone_success';
    public const EDIT_PHONE_ERROR = 'edit_phone_error';

    private $title;
    private $body;
    private $action;
    private $type;
    private $additional;

    private $actions = [
        self::ACTION_TEST,
        self::ORDER_ACCEPT,
        self::ORDER_COMPLETE,
        self::ORDER_REMIND,
        self::SERVICE_PAYMENT,
        self::CAR_MODERATE,
        self::CAN_ADD_CAR_TO_GARAGE,
        self::EMAIL_VERIFY,
        self::RECOMMEND_SERVICE,
        self::RECONCILIATION_WORK,
        self::PROMOTIONS,
        self::DISCOUNTS,
        self::EDIT_PHONE_SUCCESS,
        self::EDIT_PHONE_ERROR,
    ];

    private function __construct(string $action)
    {
        if(!in_array($action, $this->actions)){
            throw new \InvalidArgumentException("Not found this action - \"$action\" into FcmAction");
        }
    }

    public static function create(string $action, array $additional = [], $model = null): self
    {
        self::setLocaleForUser($model);

        $self = new self($action);

        $self->title = __('notification.firebase.action_'.$action.'.title');
        $self->body = __('notification.firebase.action_'.$action.'.body');
        $self->action = $action;
        $self->additional = $additional;

        if($action === static::ORDER_COMPLETE){
            $self->type = Fcm::TYPE_COMPLETE;
            if($model){
                $model->load(['service.current', 'additions.car', 'additions.brand', 'additions.model']);

                if(null != $model->additions->car){
                    $carName = $model->additions->car->car_name;
                    $number = $model->additions->car->number->getValue();
                } else {
                    $brandName = (null != $model->additions->brand_id) ? $model->additions->brand->name : null;
                    $modelName = (null != $model->additions->model_id) ? $model->additions->model->name : null;
                    $carName = "{$brandName} {$modelName}";
                    $number = null;
                }

                $self->body = __('notification.firebase.action_'.$action.'.body', [
                    'service' => $model->service->current->name ?? null,
                    'number' => $number,
                    'car' => $carName
                ]);

            }
        }
        if($action === static::SERVICE_PAYMENT){
            $self->type = Fcm::TYPE_PAYMENT;
        }
        if($action === static::CAR_MODERATE || $action === static::CAN_ADD_CAR_TO_GARAGE){
            $self->type = Fcm::TYPE_SYSTEM;
        }
        if($action === static::EMAIL_VERIFY){
            $self->type = Fcm::TYPE_EMAIL;
        }
        if($action === static::ORDER_ACCEPT){
            $self->type = Fcm::TYPE_NEW;
        }
        if($action === static::ORDER_REMIND){
            $self->type = Fcm::TYPE_MESSAGE;

            if($model){
                $self->body = __('notification.firebase.action_'.$action.'.body', [
                    'date' => $model->additions->real_date
                ]);
            }

        }
        if($action === static::RECOMMEND_SERVICE){
            /** @var $model Recommendation */
            $self->type = Fcm::TYPE_ALERT;
            $self->body = __('notification.firebase.action_'.$action.'.body', [
                'service' => $model->order->service->current->name ?? null,
                'number' => $model->car->number ?? null,
                'car' => $model->car->car_name ?? null
            ]);
        }
        if($action === static::RECONCILIATION_WORK){
            /** @var $model Agreement */
            $service = Service::query()
                ->where('alias', Service::SERVICE_ALIAS)
                ->first();

            $self->type = Fcm::TYPE_ALERT;
            $self->body = __('notification.firebase.action_'.$action.'.body', [
                'service' => $service->current->name ?? null,
                'number' => $model->car->number ?? null,
                'car' => $model->car->car_name ?? null
            ]);
        }
        if($action === static::PROMOTIONS){
            $self->type = Fcm::TYPE_PERCENT;
        }
        if($action === static::DISCOUNTS){
            $self->type = Fcm::TYPE_CUPON;
        }
        if($action === static::EDIT_PHONE_SUCCESS || $action === static::EDIT_PHONE_ERROR){
            $self->type = Fcm::TYPE_PHONE;
        }

        return $self;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAdditional()
    {
        return $this->additional;
    }

    public function getMessageAsArray(): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
        ];
    }

    // устанавливаем локаль пользователя, для формирования уведомления на языке пользователя
    private static function setLocaleForUser($model = null): void
    {
        $lang = null;
        if(null != $model){
            if($model instanceof User){
                $lang = $model->lang;
            }
            if(isset($model->user)){
                $lang = $model->user->lang;
            }
        }

        if($lang){
            \App::setLocale($lang);
        }
    }
}
