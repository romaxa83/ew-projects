<?php


namespace App\Services\SupportRequests;


use App\Contracts\Roles\HasGuardUser;
use App\Dto\SupportRequests\SupportRequestDto;
use App\Dto\SupportRequests\SupportRequestMessageDto;
use App\Exceptions\SupportRequests\SupportRequestNotFoundException;
use App\Models\Admins\Admin;
use App\Models\Support\SupportRequest;
use App\Models\Support\SupportRequestMessage;
use App\Models\Technicians\Technician;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SupportRequestService
{
    public function create(SupportRequestDto $dto, Technician $technician): SupportRequest
    {
        $supportRequest = new SupportRequest();

        $supportRequest->subject_id = $dto->getSubjectId();
        $supportRequest->technician_id = $technician->id;

        $supportRequest->save();

        $this->addMessage($supportRequest, $dto->getMessage(), $technician);

        return $supportRequest->refresh();
    }

    private function addMessage(
        SupportRequest $supportRequest,
        SupportRequestMessageDto $dto,
        HasGuardUser $user
    ): Model|SupportRequestMessage {
        return $supportRequest->messages()
            ->create(
                [
                    'message' => $dto->getText(),
                    'sender_type' => $user->getMorphType(),
                    'sender_id' => $user->getId()
                ]
            );
    }

    public function answer(
        string $supportRequestId,
        SupportRequestMessageDto $dto,
        HasGuardUser $user
    ): SupportRequestMessage {
        $supportRequest = $this->getSupportRequest($supportRequestId, $user);

        return $this->addMessage($supportRequest, $dto, $user);
    }

    private function getSupportRequest(string $supportRequestId, HasGuardUser $user): SupportRequest
    {
        $supportRequest = SupportRequest::forGuard($user)
            ->find($supportRequestId);

        if (!$supportRequest) {
            throw new SupportRequestNotFoundException();
        }

        return $supportRequest;
    }

    public function close(string $supportRequestId, HasGuardUser $user): SupportRequest
    {
        $supportRequest = $this->getSupportRequest($supportRequestId, $user);

        $supportRequest->is_closed = true;
        $supportRequest->save();

        return $supportRequest;
    }

    public function getList(array $args, HasGuardUser $user): LengthAwarePaginator
    {
        /**@var \Illuminate\Pagination\LengthAwarePaginator $paginate */
        $paginate = SupportRequest::forGuard($user)
            ->with(
                [
                    'messages',
                    'messages.sender',
                    'subject',
                    'subject.translations',
                ]
            )
            ->filter($args)
            ->sortList($user)
            ->paginate(perPage: $args['per_page'], page: $args['page']);

        return $paginate->through(
            function (SupportRequest $supportRequest) use ($user)
            {
                if ($supportRequest->is_closed) {
                    $supportRequest->is_read = true;

                    $supportRequest->messages->transform(
                        function (SupportRequestMessage $message)
                        {
                            $message->is_read = true;
                            return $message;
                        }
                    );
                    return $supportRequest;
                }

                $isRead = $supportRequest->messages->filter(
                    fn(SupportRequestMessage $message) => $message->sender_type !== $user->getMorphType()
                        && !$message->is_read
                )
                    ->pluck('is_read')
                    ->first();

                $supportRequest->is_read = $isRead === null;

                return $supportRequest;
            }
        );
    }

    public function getCounter(HasGuardUser $user): Collection
    {
        $counter = SupportRequest::forGuard($user)
            ->joinMessages()
            ->selectRaw(
                "
                    MAX(IF(sender_type = ?,1,0)) AS is_set_admin_answer,
                    SUM(
                        CASE
                            WHEN sender_type <> ? AND is_read=0 THEN 1
                            ELSE 0
                        END
                    ) as not_read,
                    COUNT(*) AS total
                ",
                [
                    Admin::MORPH_NAME,
                    $user->getMorphType()
                ]
            )
            ->where('is_closed', 0)
            ->groupBy('support_request_id')
            ->get();

        return collect(
            [
                'new' => $counter->filter(
                    fn(SupportRequest $message) => $message->is_set_admin_answer === 0
                )
                    ->count(),
                'new_messages' => $counter->sum('not_read')
            ]
        );
    }

    public function setIsRead(?int $supportRequestId, array $messagesIds, Admin|Technician $user): bool
    {
        $messages = SupportRequest::forGuard($user)
            ->joinMessages()
            ->where('sender_type', '<>', $user->getMorphType());

        if ($supportRequestId !== null) {
            $messages = $messages->where('support_request_id', $supportRequestId);

            if (!empty($messagesIds)) {
                $messages->whereIn(SupportRequestMessage::TABLE . '.id', $messagesIds);
            }
        }

        $messages->update(['is_read' => true]);

        return true;
    }
}
