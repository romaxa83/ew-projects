<?php

namespace App\Models\Saas\Support;

use App\Http\Resources\Files\ImageResource;
use App\Models\Admins\Admin;
use App\Models\BaseAuthenticatable;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class SupportRequestMessage extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaTrait;

    public const MEDIA_COLLECTION = 'support_messages_attachments';

    protected $fillable = [
        'id',
        'support_request_id',
        'message',
        'read',
        'is_question',
        'user_id',
        'admin_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'read' => 'array',
        'is_question' => 'boolean',
        'user_id' => 'int',
        'admin_id' => 'int'
    ];

    /**
     * @return BelongsTo
     */
    public function supportRequest(): BelongsTo
    {
        return $this->belongsTo(SupportRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function getAuthorData(): ?array
    {
        if ($this->user_id) {
            $role = $this->user->roles->first();
            return [
                'id' => $this->user->id,
                'full_name' => $this->user->full_name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'photo' => ImageResource::make($this->user->getFirstImage()),
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
                'is_support_employee' => false,
            ];
        }

        if ($this->admin_id) {
            return [
                'id' => null,
                'full_name' => $this->admin->full_name,
                'email' => null,
                'phone' => null,
                'photo' => ImageResource::make($this->admin->getFirstImage()),
                'role' => null,
                'is_support_employee' => true,
            ];
        }

        return null;
    }

    public function isMyMessage(BaseAuthenticatable $currentUser): bool
    {
        if ($this->user_id && $this->user_id === $currentUser->id && $currentUser instanceof User) {
            return true;
        }

        if ($this->admin_id && $this->admin_id === $currentUser->id && $currentUser instanceof Admin) {
            return true;
        }

        return false;
    }
}
