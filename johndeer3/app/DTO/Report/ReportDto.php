<?php

namespace App\DTO\Report;

use App\Models\Image;
use App\Models\User\User;

class ReportDto
{
    private $reportID;

    private $user;

    public $status;
    public $salesmanName;
    public $assignment;
    public $result;
    public $clientComment;
    public $clientEmail;
    public $plannedAt;

    public $location = null;

    private $clients = [];
    private $machines = [];
    private $features = [];
    private $images = [];
    public $signature;

    private $hasFeatures = false;
    private $hasSignature = false;

    private function __construct()
    {}

    public static function byRequest(array $args): self
    {
        $self = new self();

        $self->status = $args['status'] ?? null;
        $self->salesmanName = $args['salesman_name'] ?? null;
        $self->assignment = $args['assignment'] ?? null;
        $self->result = $args['result'] ?? null;
        $self->clientComment = $args['client_comment'] ?? null;
        $self->clientEmail = $args['client_email'] ?? null;
        $self->plannedAt = $args['planned_at'] ?? null;

        if(isset($args['location']) && !empty($args['location'])){
            $self->location = LocationDto::byArgs($args['location']);
        }

        foreach ($args['clients'] ?? [] as $client){
            $self->clients[] = ClientDto::byArgs($client);
        }

        foreach ($args['machines'] ?? [] as $machine){
            $self->machines[] = MachineDto::byArgs($machine);
        }

        if(isset($args['features']) && !empty($args['features'])){
            $self->hasFeatures = true;
            foreach ($args['features'] ?? [] as $feature){
                $self->features[] = FeatureDto::byArgs($feature);
            }
        }

        if(isset($args['files']) && !empty($args['files'])){
            foreach ($args['files'] ?? [] as $module => $files){
                if($module != Image::SIGNATURE){
                    $self->images[] = ImageDto::byArgs([
                        "module" => $module,
                        "images" => $files,
                    ]);
                }
            }
        }

        if(isset($args['files'][Image::SIGNATURE]) && !empty($args['files'][Image::SIGNATURE])){
            $self->hasSignature = true;
            $self->signature = current($args['files'][Image::SIGNATURE]);
        }

        return $self;
    }

    public function setReportID($value): self
    {
        $this->reportID = $value;
        return $this;
    }

    public function getReportID()
    {
        if(null == $this->reportID){
            throw new \Exception("Not set reportID into report dto");
        }
        return $this->reportID;
    }

    public function setUser(User $value): self
    {
        $this->user = $value;
        return $this;
    }

    public function getUserID()
    {
        if(null == $this->user){
            throw new \Exception("Not set user into report dto");
        }
        return $this->user->id;
    }

    public function getUserDealerName()
    {
        if(null == $this->user){
            throw new \Exception("Not set user into report dto");
        }
        return $this->user->dealer->name ?? null;
    }

    public function getClients(): array
    {
        return $this->clients;
    }

    public function getMachines(): array
    {
        return $this->machines;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function hasFeatures(): bool
    {
        return $this->hasFeatures;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function hasSignature(): bool
    {
        return $this->hasSignature;
    }

    public function getEgID()
    {
        return data_get($this->getMachines(), '0.egID');
    }
}
