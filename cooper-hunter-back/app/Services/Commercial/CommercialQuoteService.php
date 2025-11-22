<?php

namespace App\Services\Commercial;

use App\Contracts\Utilities\HasGeneratePdf;
use App\Dto\Commercial\CommercialQuoteAdminDto;
use App\Dto\Commercial\CommercialQuoteDto;
use App\Dto\Commercial\CommercialQuoteItemDto;
use App\Dto\Utilities\Pdf\PdfDataDto;
use App\Enums\Commercial\CommercialQuoteStatusEnum;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteHistory;
use App\Notifications\Commercial\CommercialQuoteNotification;
use App\Traits\Utilities\HasPdfService;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Carbon\CarbonImmutable;
use Core\Exceptions\TranslatedException;
use Illuminate\Support\Facades\Notification;

class CommercialQuoteService implements HasGeneratePdf
{
    use HasPdfService;

    public function __construct(protected QuoteItemService $itemService)
    {}

    public function create(
        CommercialQuoteDto $dto
    ): CommercialQuote
    {
        $model = new CommercialQuote();
        $model->commercial_project_id = $dto->getProjectId();
        $model->email = $dto->getEmail();
        $model->shipping_address = $dto->getShippingAddress();
        $model->status = $dto->getStatus();

        $model->save();

        $model->addMedia($dto->getFile())
            ->toMediaCollection(CommercialQuote::MEDIA_COLLECTION_NAME);

        return $model;
    }

    public function update(
        CommercialQuote $model,
        CommercialQuoteAdminDto $dto
    ): CommercialQuote
    {
        if($dto->hasSendDetailData()){
            $model->send_detail_data = $dto->getSendDetailData();
        }
        if($dto->hasShippingPrice()){
            $model->shipping_price = $dto->shippingPrice;
        }
        if($dto->hasTax()){
            $model->tax = $dto->tax;
        }
        if($dto->hasDiscountPercent()){
            $model->discount_percent = $dto->discountPercent;
        }
        if($dto->hasDiscountSum()){
            $model->discount_sum = $dto->discountSum;
        }
        if($dto->hasEmail()){
            $model->email = $dto->email;
        }
        if($dto->hasStatus()){
            $model = $this->setStatus($model, $dto->getStatus(), false);
        }

        $model->save();

        $model->items()->delete();

        foreach ($dto->getItems() as $item){
            /** @var $item CommercialQuoteItemDto */
            $this->itemService->create($model, $item);
        }

        return $model;
    }

    public function setStatus(
        CommercialQuote $model,
        $status,
        $save = true
    ): CommercialQuote
    {
        if($model->isPending() && $status == CommercialQuoteStatusEnum::FINAL){
            throw new TranslatedException(__('exceptions.commercial.quote.incorrect switching status'), 502);
        }

        if($model->isDone() && $status == CommercialQuoteStatusEnum::PENDING){
            throw new TranslatedException(__('exceptions.commercial.quote.incorrect switching status'), 502);
        }

        if($model->isFinal() && ($status == CommercialQuoteStatusEnum::PENDING || $status == CommercialQuoteStatusEnum::DONE)){
            throw new TranslatedException(__('exceptions.commercial.quote.incorrect switching status'), 502);
        }

        if($status == CommercialQuoteStatusEnum::DONE && !$model->isDone()){
            $model->closed_at = CarbonImmutable::now();
        }
        $model->status = $status;

        if($save){
            $model->save();
        }

        return $model;
    }

    public function generatePdf(PdfDataDto $pdfDataDto): PDF
    {
        return PdfFacade::setPaper('A4')
            ->setOptions(['isRemoteEnabled' => true])
            ->loadView(
                view: 'pdf.commercial-quote',
                data: [
                    'language' => $pdfDataDto->getLanguage(),
                    'name' => $pdfDataDto->getName(),
                    'pdf_data' => $pdfDataDto->getPdfData()
                ],
                encoding: 'UTF-8'
            );
    }

    public function sendEmail(
        CommercialQuote $quote,
        QuoteHistory $history
    )
    {
         Notification::route('mail', $quote->email)
            ->notify(new CommercialQuoteNotification($quote, $history));
    }
}
