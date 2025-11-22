<?php

namespace App\Foundations\Modules\History\Factories;

use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Models\History;
use App\Foundations\Modules\Permission\Roles\AdminRole;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Database\Factories\BaseFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Foundations\Modules\History\Models\History>
 */
class HistoryFactory extends BaseFactory
{
    protected $model = History::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $model = User::factory()->create();

        return [
            'type' => HistoryType::CHANGES(),
            'model_id' => $model->id,
            'model_type' => $model::class,
            'user_id' => $model->id,
            'user_role' => AdminRole::NAME,
            'msg' => 'history.msg',
            'msg_attr' => [],
            'performed_at' => CarbonImmutable::now(),
            'performed_timezone' => 'America/Los_Angeles',
            'details' => [],
        ];
    }
}
