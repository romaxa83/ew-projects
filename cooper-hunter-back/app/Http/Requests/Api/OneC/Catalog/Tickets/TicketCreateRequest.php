<?php

namespace App\Http\Requests\Api\OneC\Catalog\Tickets;

use App\Dto\Catalog\Tickets\TicketDto;
use App\Enums\Tickets\TicketStatusEnum;
use App\Http\Requests\BaseFormRequest;
use App\Models\Catalog\Tickets\Ticket;
use App\Models\Orders\Categories\OrderCategory;
use App\Permissions\Catalog\Products\UpdatePermission;
use App\Rules\Catalog\Tickets\TicketApiOrderPartRule;
use App\Rules\ExistsRules\SerialNumberExistsRule;
use App\Traits\Http\Requests\SimpleTranslationRulesTrait;
use Illuminate\Validation\Rule;

class TicketCreateRequest extends BaseFormRequest
{
    use SimpleTranslationRulesTrait;

    public const PERMISSION = UpdatePermission::KEY;

    public function rules(): array
    {
        return array_merge(
            [
                'serial_number' => ['required', 'string', new SerialNumberExistsRule()],
                'guid' => ['required', 'uuid', Rule::unique(Ticket::class, 'guid')],
                'code' => ['required', 'string', Rule::unique(Ticket::class, 'code')],
                'status' => ['required', TicketStatusEnum::ruleIn()],
                'case_id' => ['nullable', 'integer'],
                'order_parts' => ['nullable', 'array'],
                'order_parts.*' => ['required', new TicketApiOrderPartRule()],
            ],
            $this->getTranslationRules(),
        );
    }

    public function getDto(): TicketDto
    {
        return TicketDto::byArgs($this->getDtoArgs());
    }

    protected function getDtoArgs(): array
    {
        $args = $this->validated();

        if (0 === count($args['order_parts'] ?? [])) {
            return $args;
        }

        $parts = $args['order_parts'];

        if (array_is_list_of_string($parts)) {
            return $args;
        }

        unset($args['order_parts']);

        $guids = [];

        foreach ($parts as $part) {
            $args['order_parts'][] = data_get($part, 'value');
            $guids[] = data_get($part, 'guid');
        }

        $args['order_part_ids'] = OrderCategory::query()
            ->where('need_description', false)
            ->whereIn('guid', $guids)
            ->pluck('id')
            ->toArray();

        return $args;
    }

    protected function getTranslationFields(): array
    {
        return [
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string']
        ];
    }
}
