<?php

namespace App\Dto\Orders;

use App\Models\Orders\Order;
use Illuminate\Http\UploadedFile;

class InspectDamageDto
{

    private UploadedFile $damagePhoto;

    private ?array $damageLabels;

    /**
     * @param array $data
     * @return static
     */
    public static function byParams(array $data): self
    {
        $dto = new self();

        $dto->damagePhoto = $data[Order::INSPECTION_DAMAGE_FIELD_NAME];

        $dto->setDamageLabels($data);

        return $dto;
    }

    /**
     * @param array $data
     */
    private function setDamageLabels(array $data): void
    {
        if (empty($data['damage_labels'])) {
            return;
        }

        $this->damageLabels = array_values(array_unique($data['damage_labels']));
    }

    /**
     * @return UploadedFile
     */
    public function getDamagePhoto(): UploadedFile
    {
        return $this->damagePhoto;
    }

    /**
     * @param mixed $default
     * @return null|array
     */
    public function getDamageLabels($default = null): ?array
    {
        return !empty($this->damageLabels) ? $this->damageLabels : $default;
    }

}
