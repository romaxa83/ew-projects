<?php

namespace App\Models\Saas\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int company_id
 * @property boolean is_invoice_allowed
 */
class CompanyNotificationSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_emails',
        'receive_bol_copy_emails',
        'brokers_delivery_notification',
        'add_pickup_delivery_dates_to_bol',
        'send_bol_invoice_automatically',
        'is_invoice_allowed',
    ];

    protected $casts = [
        'notification_emails' => 'array',
        'receive_bol_copy_emails' => 'array',
        'brokers_delivery_notification' => 'boolean',
        'add_pickup_delivery_dates_to_bol' => 'boolean',
        'is_invoice_allowed' => 'boolean',
    ];

    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
