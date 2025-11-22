<?php

namespace App\Rules\Orders\Dealer;

use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use Illuminate\Contracts\Validation\Rule;

class UniqPORule implements Rule
{
    protected string $attr;

    public function __construct(protected ?Dealer $dealer = null, protected array $args)
    {}

    public function passes($attribute, $value): bool
    {
        if($this->dealer == null){
            return false;
        }

        $dealerIds = $this->dealer->company->dealers->pluck('id')->toArray();

        $this->attr = str_replace(['_', '.'], ' ', $attribute);

        return !Order::query()
            ->where('id', '!=', $this->args['id'])
            ->whereIn('dealer_id', $dealerIds)
            ->where('po', $value)
            ->exists();
    }

    public function message(): string
    {
        return __('validation.unique', ['attribute' => $this->attr]);
    }
}
