<?php


namespace App\Services\Saas\Support;


use App\Dto\Saas\Support\MessagesListDto;
use App\Models\Admins\Admin;
use App\Models\Saas\Support\SupportRequest;
use App\Models\Saas\Support\SupportRequestMessage;
use App\Models\Users\User;
use App\Services\Events\EventService;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SupportService
{

    private bool $isAdminPanel;

    /**@var Admin|User $user*/
    private $user;

    public function setIsAdminPanel(bool $isAdminPanel): SupportService
    {
        $this->isAdminPanel = $isAdminPanel;

        return $this;
    }

    /**
     * @param Admin|User $user
     * @return $this
     */
    public function setUser($user): SupportService
    {
        $this->user = $user;
        return $this;
    }


    /**
     * @param array $requestData
     * @return LengthAwarePaginator
     */
    public function getSupportRequestList(array $requestData): LengthAwarePaginator
    {
        return SupportRequest::filter($requestData)
            ->orderBy($requestData['order_by'], $requestData['order_type'])
            ->paginate(
                (int) data_get($requestData, 'per_page', 10)
            );
    }


    /**
     * @param User|null $user
     * @param array $validated
     * @return SupportRequest
     * @throws Throwable
     */
    public function createSupportRequest(?User $user, array $validated): SupportRequest
    {
        try {
            DB::beginTransaction();
            $supportRequest = new SupportRequest();

            $supportRequest->user_id = $user !== null ? $user->id : null;
            $supportRequest->status = SupportRequest::STATUS_NEW;
            $supportRequest->user_name = $validated['user_name'];
            $supportRequest->user_email = $validated['user_email'];
            $supportRequest->user_phone = $validated['user_phone'];
            $supportRequest->subject = $validated['subject'];

            if ($user !== null) {
                if ($user->isCarrier()) {
                    $supportRequest->source = SupportRequest::SOURCE_CARRIER;
                } else {
                    $supportRequest->source = SupportRequest::SOURCE_BROKER;
                }
            } else {
                $supportRequest->source = SupportRequest::SOURCE_LANDING;
            }

            $supportRequest->save();

            EventService::support($supportRequest)
                ->user($user)
                ->create()
                ->broadcast();
            $validated['new'] = true;

            $this->addMessageInSupportRequest($supportRequest, $validated);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }
        return $supportRequest;
    }

    /**
     * @param SupportRequest $supportRequest
     * @param array $validated
     * @return SupportRequestMessage
     * @throws Throwable
     */
    public function addMessageInSupportRequest(SupportRequest $supportRequest, array $validated): SupportRequestMessage
    {
        try {
            DB::beginTransaction();

            $supportMessage = new SupportRequestMessage();

            $supportMessage->support_request_id = $supportRequest->id;
            $supportMessage->message = $validated['message'];
            if ($this->isAdminPanel) {
                $supportMessage->admin_id = $this->user->id;
            } else {
                $supportMessage->user_id = $this->user->id ?? null;
            }

            if (!empty($validated['new'])) {
                $supportMessage->is_question = true;
            }

            $supportMessage->save();

            if (!$this->isAdminPanel) {
                $this->clearViewers($supportMessage);
            }

            $supportRequest->updated_at = Carbon::now()->timestamp;
            $supportRequest->save();

            if (!empty($validated['attachments'])) {
                /**@var UploadedFile $attachment*/
                foreach ($validated['attachments'] as $attachment) {
                    $supportMessage
                        ->addMedia($attachment)
                        ->setFileName(
                            media_hash_file(
                                $attachment->getClientOriginalName(),
                                $attachment->getClientOriginalExtension()
                            )
                        )->toMediaCollection(SupportRequestMessage::MEDIA_COLLECTION);
                }
            }

            DB::commit();

            EventService::support($supportRequest)
                ->user($this->user)
                ->message($supportMessage)
                ->broadcast();

        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);

            throw $e;
        }

        return $supportMessage;
    }

    private function clearViewers(SupportRequestMessage $supportRequestMessage): void
    {
        if (!$supportRequestMessage->user_id) {
            return;
        }
        $supportRequestMessage->supportRequest->viewed = null;
        $supportRequestMessage->supportRequest->save();
    }

    public function addViewer(SupportRequest $supportRequest): void
    {
        if (!is_array($supportRequest->viewed) || !in_array($this->user->id, $supportRequest->viewed, true)) {
            $supportRequest->viewed =  array_merge([$this->user->id], !is_array($supportRequest->viewed) ? [] : $supportRequest->viewed);
            $supportRequest->save();
        }
        /**@var SupportRequestMessage $questionMessage*/
        $questionMessage = $supportRequest->question();
        if (!is_array($questionMessage->read) || !in_array($this->user->id, $questionMessage->read, true)) {
            $questionMessage->read = array_merge([$this->user->id], !is_array($questionMessage->read) ? [] : $questionMessage->read);
            $questionMessage->save();
        }
    }

    public function isRead(int $supportRequestId): bool
    {
        $supportRequest = SupportRequest::find($supportRequestId);

        if ($this->isAdminPanel) {
            if ($supportRequest->admin_id !== $this->user->id) {
                return true;
            }
            return !$supportRequest->messages()->whereNull('admin_id')->where(
                function (Builder $builder) {
                    $builder->whereNull('read')->orWhereJsonDoesntContain('read', $this->user->id);
                }
            )->exists();
        }

        $exists = $supportRequest->messages()->where('user_id', $this->user->id)->exists();

        if (!$exists) {
            return true;
        }

        return !$supportRequest->messages()->whereNull('user_id')->where(
            function (Builder $builder) {
                $builder->whereNull('read')->orWhereJsonContains('read', $this->user->id);
            }
        )->exists();
    }

    public function isView(int $supportRequestId): bool
    {
        $supportRequest = SupportRequest::find($supportRequestId);

        return $supportRequest->admin_id !== null || (is_array($supportRequest->viewed) && in_array(
                    $this->user->id,
                    $supportRequest->viewed,
                    true
                ));
    }

    /**
     * @param SupportRequest $supportRequest
     * @param Collection|SupportRequestMessage $messages
     * @param User|Admin $user
     */
    public function readingMessages(SupportRequest $supportRequest, $messages, $user): void
    {
        if ($messages instanceof SupportRequestMessage) {
            $messagesId = [$messages->id];
        } else {
            $messagesId = $messages->map(
                function (SupportRequestMessage $item) {
                    return $item->id;
                }
            )->all();
        }

        if (empty($messagesId)) {
            return;
        }

        if ($this->isAdminPanel) {
            $this->readingMessagesAdmin($supportRequest, $messagesId, $user);
            return;
        }

        $this->setReader($messagesId, $user->id);
    }

    /**
     * @param SupportRequest $supportRequest
     * @param array $messages
     * @param Admin $admin
     */
    private function readingMessagesAdmin(SupportRequest $supportRequest, array $messages, Admin $admin): void
    {
        $this->addViewer($supportRequest);

        $this->setReader($messages, $admin->id);
    }

    private function setReader(array $messages, int $userId): void
    {
        $messages = SupportRequestMessage::whereIn('id', $messages)
            ->whereNotNull($this->isAdminPanel ? 'user_id' : 'admin_id')->where(
                function (Builder $builder) use ($userId) {
                    $builder->whereNull('read')->orWhereJsonDoesntContain('read', $userId);
                }
            )->get();

        if (!$messages) {
            return;
        }

        foreach ($messages as $message) {
            $message->read = array_merge([$userId], !is_array($message->read) ? [] : $message->read);
            $message->save();
        }
    }

    /**
     * @param SupportRequest $supportRequest
     * @param Admin|User $user
     * @param int|null $olderThan
     * @param int|null $newerThan
     * @param int $perPage
     * @return MessagesListDto
     */
    public function getMessagesList(SupportRequest $supportRequest, $user, ?int $olderThan, ?int $newerThan, int $perPage): MessagesListDto
    {
        $messages = $supportRequest->messages()
            ->where('is_question', false);

        if ($olderThan) {
            $messages = $messages->where('created_at', '<', date('Y-m-d H:i:s', $olderThan));

            $total = $messages->count();

            $messages = $messages->orderByDesc('id')
                ->take($perPage)
                ->get()
                ->reverse()
                ->values();
        } elseif ($newerThan) {
            $messages = $messages->where('created_at', '>', date('Y-m-d H:i:s', $newerThan));

            $total = $messages->count();

            $messages = $messages->orderBy('id')
                ->get();
        } else {
            $total = $messages->count();

            $messages = $messages->orderByDesc('id')
                ->take($perPage)
                ->get()
                ->reverse()
                ->values();
        }

        $this->readingMessages(
            $supportRequest,
            $messages,
            $user
        );

        return new MessagesListDto($messages, $total);
    }

    /**
     * @param SupportRequest $supportRequest
     * @param Admin $admin
     * @return bool
     */
    public function takeSupportRequest(SupportRequest $supportRequest, Admin $admin): bool
    {
        $supportRequest->admin_id = $admin->id;

        return $this->changeStatus($supportRequest, SupportRequest::STATUS_IN_WORK);
    }

    public function closeSupportRequest(SupportRequest $supportRequest, string $closingReason): bool
    {
        $supportRequest->closed_at = now()->timestamp;
        $supportRequest->closed_by = $this->user->full_name;
        $supportRequest->closed_by_support_employee = $this->isAdminPanel;
        $supportRequest->closing_reason = $closingReason;

        return $this->changeStatus($supportRequest, SupportRequest::STATUS_CLOSED);
    }

    /**
     * @param SupportRequest $supportRequest
     * @param int $status
     * @return bool
     */
    public function changeStatus(SupportRequest $supportRequest, int $status): bool
    {
        try {
            $supportRequest->status = $status;

            $supportRequest->save();

            EventService::support($supportRequest)
                ->user($this->user)
                ->status()
                ->broadcast();

        } catch (Exception $e) {
            Log::error($e);

            return false;
        }
        return true;
    }

    /**
     * @param int $label
     * @param SupportRequest $supportRequest
     * @return SupportRequest
     */
    public function setLabel(int $label, SupportRequest $supportRequest): SupportRequest
    {
        $supportRequest->label = $label === 0 ? null : $label;
        $supportRequest->save();

        EventService::support($supportRequest)
            ->user($this->user)
            ->label()
            ->broadcast();

        return $supportRequest;
    }

    /**
     * @param array $data
     * @param SupportRequest $supportRequest
     * @return SupportRequest
     */
    public function changeManager(array $data, SupportRequest $supportRequest): SupportRequest
    {
        $supportRequest->admin_id = data_get($data, 'manager_id');
        $supportRequest->save();

        if ($supportRequest->admin_id === null && $supportRequest->status === SupportRequest::STATUS_IN_WORK) {
            $this->changeStatus($supportRequest, SupportRequest::STATUS_NEW);
            $supportRequest->refresh();
        }

        return $supportRequest;
    }
}
