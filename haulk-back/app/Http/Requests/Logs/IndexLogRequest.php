<?php

namespace App\Http\Requests\Logs;

use App\Models\Logs\Log;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string|null message
 * @property string[]|null level_names
 * @property string date_from
 * @property string date_to
 */
class IndexLogRequest extends FormRequest
{
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level_names' => ['nullable', 'array'],
            'level_names.*' => ['string', $this->levelNames()],
            'message' => ['nullable', 'string'],
            'date_from' => ['required', 'string', $this->datetimeFormat(),],
            'date_to' => ['required', 'string', $this->datetimeFormat(),],
        ];
    }

    private function levelNames(): string
    {
        return 'in:' . implode(
                ',',
                Log::getLevels()
            );
    }

    public function getPerPage(): int
    {
        return $this->per_page ?? config('history.logs.paginate.per-page');
    }

    public function getPage(): int
    {
        return $this->page ?? 1;
    }
}
