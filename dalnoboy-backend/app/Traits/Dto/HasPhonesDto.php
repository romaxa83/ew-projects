<?php


namespace App\Traits\Dto;


use App\Dto\PhoneDto;
use JetBrains\PhpStorm\ArrayShape;

trait HasPhonesDto
{
    /**@var PhoneDto[] $phones */
    private array $phones;

    /**@var string[] $stringPhoneList */
    private array $stringPhonesList = [];

    /**
     * @return PhoneDto[]
     */
    public function getPhones(): array
    {
        return $this->phones;
    }

    /**
     * @return string[]
     */
    public function getStringPhonesList(): array
    {
        return $this->stringPhonesList;
    }

    #[ArrayShape([['phone' => "string", 'is_default' => "boolean"]])]
    protected function setPhones(
        array $phones
    ): void {
        $phonesList = [];

        foreach ($phones as $phone) {
            if (in_array($phone['phone'], $this->stringPhonesList)) {
                continue;
            }

            $this->stringPhonesList[] = $phone['phone'];

            if (!empty($isSetDefaultPhone) && !empty($phone['is_default'])) {
                $phone['is_default'] = false;
            }

            if (!empty($phone['is_default'])) {
                $isSetDefaultPhone = true;
            }

            $phonesList[] = $phone;
        }
        unset($phones);

        if (empty($isSetDefaultPhone)) {
            $phonesList[0]['is_default'] = true;
        }

        foreach ($phonesList as $phone) {
            $this->phones[] = PhoneDto::byArgs($phone);
        }
    }
}
