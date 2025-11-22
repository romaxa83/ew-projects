<?php

namespace App\Services\Commercial;

use App\Models\Admins\Admin;
use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\CommercialSettings;
use App\Models\Commercial\QuoteHistory;
use App\Models\Commercial\QuoteItem;
use App\Repositories\Commercial\CommercialQuoteHistoryRepository;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;

class CommercialQuoteHistoryService
{
    public function __construct(protected CommercialQuoteHistoryRepository $repo)
    {}

    public function create(CommercialQuote $quote, Admin $admin): QuoteHistory
    {
        $last = $this->repo->getLast($quote->id);

        $model = new QuoteHistory();
        $model->quote_id = $quote->id;
        $model->admin_id = $admin->id;
        $model->position = $last ? $last->position + 1 : 1;
        $model->estimate = $quote->estimate . '-' . $model->position;
        $model->data = $quote->load(['items'])->toJson();

        $model->save();

        return $model;
    }

    public function generateAndSavePdf(
        CommercialQuote $quote,
        ?QuoteHistory $history = null
    ): string
    {
        $now = CarbonImmutable::now()->timestamp;

        $settings = CommercialSettings::first();

        $data = collect([
            'model' => $quote,
            'history' => $history,
            'setting' => $settings
        ]);

        PdfFacade::setPaper('A4')
            ->setOptions(['isRemoteEnabled' => true])
            ->loadView(
                view: 'pdf.commercial-quote',
                data: [
                'language' => App::getLocale(),
                'name' => 'estimate',
                'pdf_data' => $data
            ],
                encoding: 'UTF-8'
            )
            ->save(
                $history
                    ? $history->getPdfStoragePath()
                    : $quote->getPdfStoragePreviewPath($now)
            )
        ;

        $url = $quote->getPreviewUrl($now);
        if($history){
            $history->addMedia($history->getPdfStoragePath())
                ->toMediaCollection(QuoteHistory::MEDIA_COLLECTION_NAME);

            $url = $history->getFirstMediaUrl(QuoteHistory::MEDIA_COLLECTION_NAME);
        }

        return $url;
    }

    public function setQuoteFromHistory(QuoteHistory $history): CommercialQuote
    {
        $data = jsonToArray($history->data);

        $quote = new CommercialQuote();
        $quote->id = data_get($data, 'id');
        $quote->commercial_project_id = data_get($data, 'commercial_project_id');
        $quote->email = data_get($data, 'email');
        $quote->shipping_address = data_get($data, 'shipping_address');
        $quote->status = data_get($data, 'status');
        $quote->sort = data_get($data, 'sort');
        $quote->created_at = data_get($data, 'created_at');
        $quote->updated_at = data_get($data, 'updated_at');
        $quote->closed_at = data_get($data, 'closed_at');
        $quote->send_detail_data = data_get($data, 'send_detail_data');
        $quote->count_email_sending = data_get($data, 'count_email_sending');
        $quote->shipping_price = data_get($data, 'shipping_price');
        $quote->tax = data_get($data, 'tax');
        $quote->discount_percent = data_get($data, 'discount_percent');
        $quote->discount_sum = data_get($data, 'discount_sum');

        foreach (data_get($data, 'items', []) as $item){
            $i = new QuoteItem();
            $i->id = data_get($item, 'id');
            $i->commercial_quote_id = data_get($item, 'commercial_quote_id');
            $i->product_id = data_get($item, 'product_id');
            $i->name = data_get($item, 'name');
            $i->price = data_get($item, 'price');
            $i->qty = data_get($item, 'qty');
            $i->created_at = data_get($item, 'created_at');
            $i->updated_at = data_get($item, 'updated_at');
        }

        return $quote;
    }
}
