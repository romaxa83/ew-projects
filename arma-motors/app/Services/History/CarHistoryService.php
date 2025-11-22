<?php

namespace App\Services\History;

use App\DTO\History\HistoryCarDto;
use App\DTO\History\InvoiceDto;
use App\DTO\History\InvoicePartDto;
use App\DTO\History\OrderCustomerDto;
use App\DTO\History\OrderDispatcherDto;
use App\DTO\History\OrderDto;
use App\DTO\History\OrderJobDto;
use App\DTO\History\OrderOrganizationDto;
use App\DTO\History\OrderOwnerDto;
use App\DTO\History\OrderPartDto;
use App\DTO\History\OrderPayerDto;
use App\Models\History\CarItem;
use App\Models\History\Invoice;
use App\Models\History\InvoicePart;
use App\Models\History\Order;
use App\Models\Order\Order as OrderModel;
use App\Models\History\OrderCustomer;
use App\Models\History\OrderDispatcher;
use App\Models\History\OrderJob;
use App\Models\History\OrderOrganization;
use App\Models\History\OrderOwner;
use App\Models\History\OrderPart;
use App\Models\History\OrderPayer;
use App\Repositories\History\CarHistoryRepository;
use App\Services\Media\File\FileService;
use App\Traits\DateConvert;

final class CarHistoryService
{
    use DateConvert;

    public function __construct(
        protected CarHistoryRepository $repo,
        protected FileService $serviceFile,
    )
    {}

    public function createOrUpdate(HistoryCarDto $dto): CarItem
    {
        try {
            /** @var $model CarItem  */
            $model = $this->repo->getOneBy('car_uuid', $dto->uuid_car, [
                'invoices.parts'
            ]);
            if($model){
                $model = $this->update($model, $dto);
            } else {
                $model = $this->create($dto);
            }

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function create(HistoryCarDto $dto): CarItem
    {
        $model = new CarItem();
        $model->car_uuid = $dto->uuid_car;
        $model->save();

        foreach ($dto->invoices as $dtoInvoice){
            $this->createInvoice($dtoInvoice, $model);
        }
        foreach ($dto->orders as $dtoOrder){
            $this->createOrder($dtoOrder, $model);
        }

        return $model;
    }

    public function update(CarItem $model, HistoryCarDto $dto): CarItem
    {
        foreach ($dto->invoices as $dtoInvoice){
            /** @var $dtoInvoice InvoiceDto */
            if($invoice = $model->invoices->where('aa_uuid', $dtoInvoice->aa_id)->first()){
                $this->updateInvoice($dtoInvoice, $invoice);
            } else {
                $this->createInvoice($dtoInvoice, $model);
            }
        }

        foreach ($dto->orders as $dtoOrder){
            /** @var $dtoOrder OrderDto */
            if($order = $model->orders->where('aa_id', $dtoOrder->aa_id)->first()){
                $this->updateOrder($dtoOrder, $order);
            } else {
                $this->createOrder($dtoOrder, $model);
            }
        }

        return $model;
    }

    public function createInvoice(InvoiceDto $dto, CarItem $related): Invoice
    {
        $model = new Invoice();
        $model->row_id = $related->id;
        $model->aa_uuid = $dto->aa_id;
        $model->address = $dto->address;
        $model->amount_including_vat = $dto->amount_including_vat;
        $model->amount_vat = $dto->amount_vat;
        $model->amount_without_vat = $dto->amount_without_vat;
        $model->author = $dto->author;
        $model->contact_information = $dto->contact_information;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->discount = $dto->discount;
        $model->etc = $dto->etc;
        $model->number = $dto->number;
        $model->organization = $dto->organization;
        $model->phone = $dto->phone;
        $model->shopper = $dto->shopper;
        $model->tax_code = $dto->tax_code;

        $model->save();

        foreach ($dto->parts as $dtoInvoicePart){
            $this->createInvoicePart($dtoInvoicePart, $model);
        }

        $this->generateInvoicePdf($dto, $model);

        return $model;
    }

    public function updateInvoice(InvoiceDto $dto, Invoice $model): Invoice
    {
        $model->address = $dto->address;
        $model->amount_including_vat = $dto->amount_including_vat;
        $model->amount_vat = $dto->amount_vat;
        $model->amount_without_vat = $dto->amount_without_vat;
        $model->author = $dto->author;
        $model->contact_information = $dto->contact_information;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->discount = $dto->discount;
        $model->etc = $dto->etc;
        $model->number = $dto->number;
        $model->organization = $dto->organization;
        $model->phone = $dto->phone;
        $model->shopper = $dto->shopper;
        $model->tax_code = $dto->tax_code;

        $model->save();

        $model->parts()->delete();
        foreach ($dto->parts as $dtoInvoicePart){
            $this->createInvoicePart($dtoInvoicePart, $model);
        }

        $this->generateInvoicePdf($dto, $model);

        return $model;
    }

    public function createInvoicePart(InvoicePartDto $dto, Invoice $related): InvoicePart
    {
        $model = new InvoicePart();
        $model->row_id = $related->id;
        $model->name = $dto->name;
        $model->ref = $dto->ref;
        $model->unit = $dto->unit;
        $model->discounted_price = $dto->discounted_price;
        $model->price = $dto->price;
        $model->quantity = $dto->quantity;
        $model->rate = $dto->rate;
        $model->sum = $dto->sum;

        $model->save();

        return $model;
    }

    public function createOrder(OrderDto $dto, CarItem $related): Order
    {
        $model = new Order();
        $model->row_id = $related->id;
        $model->aa_id = $dto->aa_id;
        $model->amount_in_words = $dto->amount_in_words;
        $model->amount_including_vat = $dto->amount_including_vat;
        $model->amount_vat = $dto->amount_vat;
        $model->amount_without_vat = $dto->amount_without_vat;
        $model->body_number = $dto->body_number;
        $model->closing_date = $this->fromAAToTimestamp($dto->closing_date);
        $model->current_account = $dto->current_account;
        $model->date = $dto->date;
        $model->date_of_sale = $this->fromAAToTimestamp($dto->date_of_sale);
        $model->dealer = $dto->dealer;
        $model->disassembled_parts = $dto->disassembled_parts;
        $model->discount = $dto->discount;
        $model->discount_jobs = $dto->discount_jobs;
        $model->discount_parts = $dto->discount_parts;
        $model->jobs_amount_including_vat = $dto->jobs_amount_including_vat;
        $model->jobs_amount_vat = $dto->jobs_amount_vat;
        $model->jobs_amount_without_vat = $dto->jobs_amount_without_vat;
        $model->model = $dto->model;
        $model->number = $dto->number;
        $model->parts_amount_including_vat = $dto->parts_amount_including_vat;
        $model->parts_amount_vat = $dto->parts_amount_vat;
        $model->parts_amount_without_vat = $dto->parts_amount_without_vat;
        $model->producer = $dto->producer;
        $model->recommendations = $dto->recommendations;
        $model->repair_type = $dto->repair_type;
        $model->state_number = $dto->state_number;
        $model->mileage = $dto->mileage;

        $model->save();

        foreach ($dto->parts as $dtoOrderPart){
            $this->createOrderPart($dtoOrderPart, $model);
        }

        foreach ($dto->jobs as $dtoOrderJob){
            $this->createOrderJob($dtoOrderJob, $model);
        }

        if($dto->customer){
            $this->createOrderCustomer($dto->customer, $model);
        }

        if($dto->dispatcher){
            $this->createOrderDispatcher($dto->dispatcher, $model);
        }

        if($dto->organization){
            $this->createOrderOrganization($dto->organization, $model);
        }

        if($dto->owner){
            $this->createOrderOwner($dto->owner, $model);
        }

        if($dto->payer){
            $this->createOrderPayer($dto->payer, $model);
        }

        $this->generateOrderPdf($dto, $model);

        return $model;
    }

    public function updateOrder(OrderDto $dto, Order $model): Order
    {
        $model->amount_in_words = $dto->amount_in_words;
        $model->amount_including_vat = $dto->amount_including_vat;
        $model->amount_vat = $dto->amount_vat;
        $model->amount_without_vat = $dto->amount_without_vat;
        $model->body_number = $dto->body_number;
        $model->closing_date = $this->fromAAToTimestamp($dto->closing_date);
        $model->current_account = $dto->current_account;
        $model->date = $dto->date;
        $model->date_of_sale = $this->fromAAToTimestamp($dto->date_of_sale);
        $model->dealer = $dto->dealer;
        $model->disassembled_parts = $dto->disassembled_parts;
        $model->discount = $dto->discount;
        $model->discount_jobs = $dto->discount_jobs;
        $model->discount_parts = $dto->discount_parts;
        $model->jobs_amount_including_vat = $dto->jobs_amount_including_vat;
        $model->jobs_amount_vat = $dto->jobs_amount_vat;
        $model->jobs_amount_without_vat = $dto->jobs_amount_without_vat;
        $model->model = $dto->model;
        $model->number = $dto->number;
        $model->parts_amount_including_vat = $dto->parts_amount_including_vat;
        $model->parts_amount_vat = $dto->parts_amount_vat;
        $model->parts_amount_without_vat = $dto->parts_amount_without_vat;
        $model->producer = $dto->producer;
        $model->recommendations = $dto->recommendations;
        $model->repair_type = $dto->repair_type;
        $model->state_number = $dto->state_number;
        $model->mileage = $dto->mileage;

        $model->save();

        $model->parts()->delete();
        foreach ($dto->parts as $dtoOrderPart){
            $this->createOrderPart($dtoOrderPart, $model);
        }

        $model->jobs()->delete();
        foreach ($dto->jobs as $dtoOrderJob){
            $this->createOrderJob($dtoOrderJob, $model);
        }

        if($dto->customer){
            if($model->customer){
                $this->updateOrderCustomer($dto->customer, $model->customer);
            } else {
                $this->createOrderCustomer($dto->customer, $model);
            }
        }

        if($dto->dispatcher){
            if($model->dispatcher){
                $this->updateOrderDispatcher($dto->dispatcher, $model->dispatcher);
            } else {
                $this->createOrderDispatcher($dto->dispatcher, $model);
            }
        }

        if($dto->organization){
            if($model->organization){
                $this->updateOrderOrganization($dto->organization, $model->organization);
            } else {
                $this->createOrderOrganization($dto->organization, $model);
            }
        }

        if($dto->owner){
            if($model->owner){
                $this->updateOrderOwner($dto->owner, $model->owner);
            } else {
                $this->createOrderOwner($dto->owner, $model);
            }
        }

        if($dto->payer){
            if($model->payer){
                $this->updateOrderPayer($dto->payer, $model->payer);
            } else {
                $this->createOrderPayer($dto->payer, $model);
            }
        }

        $this->generateOrderPdf($dto, $model);

        return $model;
    }

    public function generateOrderPdf(OrderDto $dto, Order $model): void
    {
        if(isset($model->file->path) && file_exists($model->file->path)){
            unlink($model->file->path);
        }
        $this->serviceFile->generateOrderHistoryPDF($model, $dto->pdfData, OrderModel::FILE_ACT_TYPE);
    }

    public function generateInvoicePdf(InvoiceDto $dto, Invoice $model): void
    {
        if(isset($model->file->path) && file_exists($model->file->path)){
            unlink($model->file->path);
        }

        $this->serviceFile->generateInvoiceHistoryPDF($model, $dto->pdfData, OrderModel::FILE_BILL_TYPE);
    }

    public function createOrderPart(OrderPartDto $dto, Order $related): OrderPart
    {
        $model = new OrderPart();
        $model->row_id = $related->id;
        $model->name = $dto->name;
        $model->amount_including_vat = $dto->amount_including_vat;
        $model->amount_without_vat = $dto->amount_without_vat;
        $model->price = $dto->price;
        $model->price_with_vat = $dto->price_with_vat;
        $model->price_without_vat = $dto->price_without_vat;
        $model->producer = $dto->producer;
        $model->rate = $dto->rate;
        $model->quantity = $dto->quantity;
        $model->unit = $dto->unit;
        $model->ref = $dto->ref;

        $model->save();

        return $model;
    }

    public function createOrderJob(OrderJobDto $dto, Order $related): OrderJob
    {
        $model = new OrderJob();
        $model->row_id = $related->id;
        $model->name = $dto->name;
        $model->amount_including_vat = $dto->amount_including_vat;
        $model->amount_without_vat = $dto->amount_without_vat;
        $model->coefficient = $dto->coefficient;
        $model->price = $dto->price;
        $model->price_with_vat = $dto->price_with_vat;
        $model->price_without_vat = $dto->price_without_vat;
        $model->rate = $dto->rate;
        $model->ref = $dto->ref;

        $model->save();

        return $model;
    }

    public function createOrderCustomer(OrderCustomerDto $dto, Order $related): OrderCustomer
    {
        $model = new OrderCustomer();
        $model->row_id = $related->id;
        $model->fio = $dto->fio;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->email = $dto->email;
        $model->name = $dto->name;
        $model->number = $dto->number;
        $model->phone = $dto->phone;

        $model->save();

        return $model;
    }

    public function updateOrderCustomer(OrderCustomerDto $dto, OrderCustomer $model): OrderCustomer
    {
        $model->fio = $dto->fio;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->email = $dto->email;
        $model->name = $dto->name;
        $model->number = $dto->number;
        $model->phone = $dto->phone;

        $model->save();

        return $model;
    }

    public function createOrderDispatcher(OrderDispatcherDto $dto, Order $related): OrderDispatcher
    {
        $model = new OrderDispatcher();
        $model->row_id = $related->id;
        $model->fio = $dto->fio;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->name = $dto->name;
        $model->number = $dto->number;
        $model->position = $dto->position;

        $model->save();

        return $model;
    }

    public function updateOrderDispatcher(OrderDispatcherDto $dto, OrderDispatcher $model): OrderDispatcher
    {
        $model->fio = $dto->fio;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->name = $dto->name;
        $model->number = $dto->number;
        $model->position = $dto->position;

        $model->save();

        return $model;
    }

    public function createOrderOrganization(OrderOrganizationDto $dto, Order $related): OrderOrganization
    {
        $model = new OrderOrganization();
        $model->row_id = $related->id;
        $model->address = $dto->address;
        $model->name = $dto->name;
        $model->phone = $dto->phone;

        $model->save();

        return $model;
    }

    public function updateOrderOrganization(OrderOrganizationDto $dto, OrderOrganization $model): OrderOrganization
    {
        $model->address = $dto->address;
        $model->name = $dto->name;
        $model->phone = $dto->phone;

        $model->save();

        return $model;
    }

    public function createOrderOwner(OrderOwnerDto $dto, Order $related): OrderOwner
    {
        $model = new OrderOwner();
        $model->row_id = $related->id;
        $model->address = $dto->address;
        $model->name = $dto->name;
        $model->email = $dto->email;
        $model->certificate = $dto->certificate;
        $model->phone = $dto->phone;
        $model->etc = $dto->etc;

        $model->save();

        return $model;
    }

    public function updateOrderOwner(OrderOwnerDto $dto, OrderOwner $model): OrderOwner
    {
        $model->address = $dto->address;
        $model->name = $dto->name;
        $model->email = $dto->email;
        $model->certificate = $dto->certificate;
        $model->phone = $dto->phone;
        $model->etc = $dto->etc;

        $model->save();

        return $model;
    }

    public function createOrderPayer(OrderPayerDto $dto, Order $related): OrderPayer
    {
        $model = new OrderPayer();
        $model->row_id = $related->id;
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->name = $dto->name;
        $model->number = $dto->number;
        $model->contract = $dto->contract;

        $model->save();

        return $model;
    }

    public function updateOrderPayer(OrderPayerDto $dto, OrderPayer $model): OrderPayer
    {
        $model->date = $this->fromAAToTimestamp($dto->date);
        $model->name = $dto->name;
        $model->number = $dto->number;
        $model->contract = $dto->contract;

        $model->save();

        return $model;
    }
}
