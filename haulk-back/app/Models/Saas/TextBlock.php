<?php

namespace App\Models\Saas;

use App\ModelFilters\Saas\TextBlockFilter;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TextBlock extends Model
{
    use HasFactory;
    use Filterable;

    public const TABLE_NAME = 'text_blocks';

    public const TB_GROUP_BILLING = 'billing';

    public const TB_GROUPS = [
        self::TB_GROUP_BILLING => 'Billing'
    ];

    public const TB_SCOPE_CARRIER = 'carrier';
    public const TB_SCOPE_BROKER = 'broker';
    public const TB_SCOPE_BACKOFFICE = 'backoffice';

    public const TB_SCOPES = [
        self::TB_SCOPE_CARRIER => 'Carrier',
        self::TB_SCOPE_BROKER => 'Broker',
        self::TB_SCOPE_BACKOFFICE => 'BackOffice'
    ];

    public $fillable = [
        'id',
        'block',
        'group',
        'scope',
        'en',
        'es',
        'ru'
    ];

    public $casts = [
        'scope' => 'array'
    ];

    public function modelFilter(): string
    {
        return TextBlockFilter::class;
    }
}
