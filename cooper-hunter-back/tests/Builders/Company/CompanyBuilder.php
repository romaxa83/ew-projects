<?php

namespace Tests\Builders\Company;

use App\Enums\Companies\CompanyStatus;
use App\Enums\Companies\ContactType;
use App\Models\Companies\CommercialManager;
use App\Models\Companies\Company;
use App\Models\Companies\Contact;
use App\Models\Companies\Corporation;
use App\Models\Companies\Manager;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\BaseBuilder;

class CompanyBuilder extends BaseBuilder
{
    protected bool $withContacts = false;
    protected bool $withCorporation = false;
    protected bool $withManager = false;
    protected bool $withCommercialManager = false;

    function modelClass(): string
    {
        return Company::class;
    }

    public function withContacts(): self
    {
        $this->withContacts = true;
        return $this;
    }

    public function withGuid(?string $guid = null): self
    {
        if(!$guid){
            $guid = $this->faker->uuid;
        }
        $this->data['guid'] = $guid;
        return $this;
    }

    public function withCorporation(): self
    {
        $this->withCorporation = true;
        return $this;
    }

    public function withManager(): self
    {
        $this->withManager = true;
        return $this;
    }

    public function withCommercialManager(): self
    {
        $this->withCommercialManager = true;
        return $this;
    }

    public function setCorporation(Corporation $model): self
    {
        $this->data['corporation_id'] = $model->id;
        return $this;
    }

    public function setStatus(CompanyStatus $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }

    protected function beforeSave(): void
    {
        if($this->withCorporation){
            $corp = Corporation::factory()->create();

            $this->data['corporation_id'] = $corp->id;
        }
    }

    protected function afterSave($model): void
    {
        if($this->withContacts){
            Contact::factory()->create([
                'type' => ContactType::ACCOUNT,
                'company_id' => $model->id
            ]);
            Contact::factory()->create([
                'type' => ContactType::ORDER,
                'company_id' => $model->id
            ]);
        }
        if($this->withManager){
            Manager::factory()->create([
                'company_id' => $model->id
            ]);
        }
        if($this->withCommercialManager){
            CommercialManager::factory()->create([
                'company_id' => $model->id
            ]);
        }
    }

    protected function afterClear(): void
    {
        $this->withContacts = false;
        $this->withCorporation = false;
        $this->withManager = false;
        $this->withCommercialManager = false;
    }
}

