<?php

namespace Database\Factories\Commercial\Commissioning;

use App\Enums\Commercial\Commissioning\ProtocolStatus;
use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\Commissioning\ProjectProtocol;
use App\Models\Commercial\Commissioning\Protocol;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ProjectProtocol[]|ProjectProtocol create(array $attributes = [])
 */
class ProjectProtocolFactory extends BaseFactory
{
    protected $model = ProjectProtocol::class;

    public function definition(): array
    {
        return [
            'project_id' => CommercialProject::factory(),
            'protocol_id' => Protocol::factory(),
            'status' => ProtocolStatus::DRAFT(),
            'sort' => 1,
            'closed_at' => null,
        ];
    }
}


