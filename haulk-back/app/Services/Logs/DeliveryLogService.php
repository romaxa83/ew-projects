<?php

namespace App\Services\Logs;

use App\Events\ModelChanged;
use App\Models\Orders\Order;
use App\Models\Orders\OrderSignature;
use App\Models\Users\User;
use App\Notifications\Orders\SendDocs;
use Illuminate\Notifications\Messages\MailMessage;

class DeliveryLogService
{

    public const EMAIL_RESULT_SUCCESS = 'success';
    public const EMAIL_RESULT_FAIL = 'fail';

    public const EMAIL_SIGNATURE_LINK_TYPE = 'signature_link';

    private array $documentDescription;


    public function enableEmailTracking(MailMessage $mail, string $orderId, string $type): void
    {
        $mail->withSwiftMessage(function ($message) use ($orderId, $type) {
            $message->getHeaders()->addTextHeader(config('emaillog.headers.order_id'), $orderId);
            $message->getHeaders()->addTextHeader(config('emaillog.headers.email_type'), $type);

            $envType = config('emaillog.env_type');

            if ($envType !== null) {
                $message->getHeaders()->addTextHeader(config('emaillog.headers.env_type'), $envType);
            }
        });
    }

    /**
     * @param array|string $attachments
     * @return array
     */
    public function createDocumentDescription($attachments): array
    {
        $this->documentDescription = [];

        $attachments = collect(is_array($attachments) ? $attachments : explode(",", $attachments));

        $document = $attachments->last();

        $attachments = $attachments->slice(0, -1);

        if ($attachments->isEmpty()) {
            $this->documentDescription = [
                'documents' => null,
                'document' => ucfirst(SendDocs::ATTACHMENTS_NAME[$document])
            ];

            return $this->documentDescription;
        }

        $this->documentDescription = [
            'documents' => ucfirst($attachments->map(
                    fn($item): string => SendDocs::ATTACHMENTS_NAME[$item]
                )
                ->implode(', ')
            ),
            'document' => SendDocs::ATTACHMENTS_NAME[$document]
        ];
        return $this->documentDescription;
    }

    public function logSentDocsViaEmail(Order $order, User $sender, array $emails, bool $auto = false): void
    {
        event(
            new ModelChanged(
                $order,
                'history.sent_doc' . ($this->documentDescription['documents'] !== null ? 's' : '') . ($auto === false ? '' : '_auto'),
                [
                    'documents' => $this->documentDescription['documents'],
                    'document' => $this->documentDescription['document'],
                    'full_name' => $sender->full_name,
                    'emails' => implode(',', $emails)
                ]
            )
        );
    }

    public function logSentDocsViaFax(Order $order, User $sender, string $fax): void
    {
        event(
            new ModelChanged(
                $order,
                'history.sending_doc' . ($this->documentDescription['documents'] !== null ? 's' : '') . '_to_fax',
                [
                    'documents' => $this->documentDescription['documents'],
                    'document' => $this->documentDescription['document'],
                    'full_name' => $sender->full_name,
                    'number' => phone_format($fax),
                ]
            )
        );
    }

    public function logFailAutomaticSendDocViaEmail(Order $order, string $doc, string $message = '')
    {
        event(
            new ModelChanged(
                $order,
                'history.send_' .$doc. '_failed',
                [
                    'message' => !empty($message) ? ' ' . $message : ''
                ]
            )
        );
    }

    public function processEmailLogs(array $logs): void
    {
        $envType = config('emaillog.env_type');

        foreach ($logs as $record) {
            if ($record['env_type'] !== $envType) {
                continue;
            }

            if (!in_array($record['result'], [self::EMAIL_RESULT_SUCCESS, self::EMAIL_RESULT_FAIL], true)) {
                continue;
            }

            $this->checkEmailType($record);

        }
    }

    private function checkEmailType(array $record)
    {
        switch ($record['type']) {
            case self::EMAIL_SIGNATURE_LINK_TYPE:
                return $this->checkDeliveredSignatureLink($record);
            default:
                return $this->checkDeliveredDocs($record);
        }
    }

    private function checkDeliveredSignatureLink(array $record)
    {
        /**@var OrderSignature $signature*/
        $signature = OrderSignature::where('signature_token', $record['order_id'])->first();

        if (!$signature) {
            return null;
        }

        event(
            new ModelChanged(
                $signature->order,
                'history.delivered_signature_link_' . $record['result'],
                [
                    'location' => $signature->inspection_location,
                    'email_recipient' => $record['recipient_email']
                ]
            )
        );

        return null;
    }

    private function checkDeliveredDocs(array $record)
    {
        $order = Order::where('public_token', $record['order_id'])->first();

        if (!$order) {
            return null;
        }

        $documentDescription = $this->createDocumentDescription($record['type']);

        event(
            new ModelChanged(
                $order,
                'history.delivered_doc' . ($documentDescription['documents'] !== null ? 's' : '') . '_' . $record['result'],
                [
                    'documents' => $documentDescription['documents'],
                    'document' => $documentDescription['document'],
                    'email' => $record['recipient_email'],
                ],
                null,
                $order->user->getCompany()->getTimezone()
            )
        );
    }
}
