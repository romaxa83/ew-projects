<?php

namespace App\Models\Saas\Company;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPaymentContact extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'email',
        'use_accounting_contact',
    ];

    protected $casts = [
        'use_accounting_contact' => 'boolean',
    ];

    public function useAccountingContact(): bool
    {
        return $this->use_accounting_contact;
    }
}
