<?php

namespace Tests\_Helpers;

use App\Models\Catalogs\Service\Service;
use App\Models\Order\Additions;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Types\Communication;
use App\Types\Order\PaymentStatus;
use App\Types\Order\Status;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class OrderBuilder
{
    private $uuid;
    private $serviceId;
    private $userId;
    private null|int $adminId = null;
    private $communication;
    private $status;
    private $type = Order::TYPE_ORDINARY;
    private $paymentStatus;
    private $count = 1;

    private $brandId = null;
    private $modelId = null;
    private $dealershipId = null;
    private $carId = null;
    private $responsible = null;
    private $recommendationId = null;
    private $postUuid = null;

    private $asOne = false;
    private $withAdditions = false;
    private bool $softDeleted = false;

    private $closed_at = null;
    private $on_date = null;
    private $real_date = null;
    private $for_current_filter_date = null;

    // ServiceID
    public function getServiceId()
    {
        if(null == $this->serviceId){
            $this->setServiceId(Service::select('id')->orderBy(\DB::raw('RAND()'))->first()->id);
        }

        return $this->serviceId;
    }
    public function setServiceId(int $serviceId): self
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    // RecommendationID
    public function getRecommendationId()
    {
        return $this->recommendationId;
    }
    public function setRecommendationId(int $value): self
    {
        $this->recommendationId = $value;

        return $this;
    }
    // UserID
    public function getUserId()
    {
        if(null == $this->userId){
            $user = User::factory()->create();
            $this->setUserId($user->id);
        }

        return $this->userId;
    }
    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
    // UUID
    public function getUuid()
    {
        return $this->uuid;
    }
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
    // BrandID
    public function geBrandId()
    {
        return $this->brandId;
    }
    public function setBrandId(int $brandId): self
    {
        $this->brandId = $brandId;
        $this->withAdditions();

        return $this;
    }
    // ModelID
    public function geModelId()
    {
        return $this->modelId;
    }
    public function setModelId(int $modelId): self
    {
        $this->modelId = $modelId;
        $this->withAdditions();

        return $this;
    }
    // AAPostID
    public function getPostUuid()
    {
        return $this->postUuid;
    }
    public function setPostUuid($value): self
    {
        $this->postUuid = $value;
        $this->withAdditions();

        return $this;
    }
    // DealershipID
    public function geDealershipId()
    {
        return $this->dealershipId;
    }
    public function setDealershipId(int $dealershipId): self
    {
        $this->dealershipId = $dealershipId;
        $this->withAdditions();

        return $this;
    }
    // CarID
    public function geCarId()
    {
        return $this->carId;
    }
    public function setCarId(int $carId): self
    {
        $this->carId = $carId;
        $this->withAdditions();

        return $this;
    }
    // Responsible
    public function getResponsible()
    {
        return $this->responsible;
    }
    public function setResponsible(string $responsible): self
    {
        $this->responsible = $responsible;
        $this->withAdditions();

        return $this;
    }
    // AdminID
    public function getAdminId()
    {
        return $this->adminId;
    }
    public function setAdminId(int $adminId): self
    {
        $this->adminId = $adminId;

        return $this;
    }
    // Communication
    public function getCommunication()
    {
        if(null == $this->communication){
            $this->setCommunication(Communication::TELEGRAM);
        }

        return $this->communication;
    }
    public function setCommunication(string $communication): self
    {
        $this->communication = $communication;

        return $this;
    }
    // Status
    public function getStatus()
    {
        if(null == $this->status){
            $this->setStatus(Status::DRAFT);
        }

        return $this->status;
    }
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }
    // Payment status
    public function getPaymentStatus()
    {
        if(null == $this->paymentStatus){
            $this->setPaymentStatus(PaymentStatus::NONE);
        }

        return $this->paymentStatus;
    }
    public function setPaymentStatus($status): self
    {
        $this->paymentStatus = $status;

        return $this;
    }
    // Closed At
    public function getClosedAt()
    {
        return $this->closed_at;
    }
    public function setClosedAt($closedAt): self
    {
        $this->closed_at = $closedAt;

        return $this;
    }

    // On Date
    public function getOnDate()
    {
        return $this->on_date;
    }
    public function setOnDate($onDate): self
    {
        $this->on_date = $onDate;
        $this->for_current_filter_date = $this->on_date;
        $this->withAdditions();

        return $this;
    }
    // Real Date
    public function getRealDate()
    {
        return $this->real_date;
    }
    public function setRealDate($realDate): self
    {
        $this->real_date = $realDate;
        $this->for_current_filter_date = $this->real_date;
        $this->withAdditions();

        return $this;
    }

    // Count
    public function getCount()
    {
        return $this->count;
    }
    public function setCount($count): self
    {
        $this->count = $count;

        return $this;
    }

    public function asOne(): self
    {
        $this->asOne = true;

        return $this;
    }

    public function withAdditions(): self
    {
        $this->withAdditions = true;

        return $this;
    }

    public function softDeleted(): self
    {
        $this->softDeleted = true;

        return $this;
    }

    public function create()
    {
        $order = $this->save();

        if($this->withAdditions){
            $data = [
                'brand_id' => $this->geBrandId(),
                'model_id' => $this->geModelId(),
                'dealership_id' => $this->geDealershipId(),
                'car_id' => $this->geCarId(),
                'responsible' => $this->getResponsible(),
                'on_date' => $this->getOnDate(),
                'real_date' => $this->getRealDate(),
                'for_current_filter_date' => $this->for_current_filter_date,
                'recommendation_id' => $this->getRecommendationId(),
                'post_uuid' => $this->getPostUuid()
            ];

            if($order instanceof Collection){
                foreach ($order as $item){
                    $data['order_id'] = $item->id;
                    Additions::factory()->create($data);
                }
            } else {
                $data['order_id'] = $order->id;
                Additions::factory()->create($data);
            }
        }
        $this->clear();

        return $order;
    }

    private function save()
    {
        $data = [
            'uuid' => $this->getUuid(),
            'service_id' => $this->getServiceId(),
            'user_id' => $this->getUserId(),
            'admin_id' => $this->getAdminId(),
            'communication' => $this->getCommunication(),
            'status' => $this->getStatus(),
            'type' => $this->type,
            'payment_status' => $this->getPaymentStatus(),
            'closed_at' => $this->getClosedAt(),
        ];

        if($this->softDeleted){
            $data['deleted_at'] = Carbon::now();
        }

        if($data['status'] == Status::CLOSE && null == $this->getClosedAt() ){
            $data['closed_at'] = Carbon::now();
        }

        if($this->asOne){
            return Order::factory()->new($data)->create();
        }

        return Order::factory()->new($data)->count($this->getCount())->create();
    }
    private function clear()
    {
        $this->uuid = null;
        $this->serviceId = null;
        $this->userId = null;
        $this->adminId = null;
        $this->status = null;
        $this->type = Order::TYPE_ORDINARY;
        $this->paymentStatus = null;
        $this->communication = null;
        $this->count = 1;
        $this->asOne = false;
        $this->withAdditions = false;
        $this->brandId = null;
        $this->modelId = null;
        $this->dealershipId = null;
        $this->carId = null;
        $this->responsible = null;
        $this->closed_at = null;
        $this->on_date = null;
        $this->real_date = null;
        $this->for_current_filter_date = null;
        $this->recommendationId = null;
        $this->postUuid = null;
    }
}
