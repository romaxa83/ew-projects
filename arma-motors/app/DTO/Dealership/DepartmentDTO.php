<?php

namespace App\DTO\Dealership;

use App\DTO\NameTranslationDTO;
use App\Models\Dealership\DepartmentTranslation;
use App\Traits\AssetData;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class DepartmentDTO
{
    use AssetData;

    private null|Phone $phone;
    private null|Email $email;
    private null|string $viber;
    private null|string $telegram;
    private int $sort;
    private $type;
    private bool $active;
    private null|Point $location;
    private array $translations = [];
    private array $schedules = [];

    private function __construct()
    {}

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->phone = isset($args['phone']) ? new Phone($args['phone']) : null;
        $self->email = isset($args['email']) ? new Email($args['email']) : null;
        $self->viber = $args['viber'] ?? null;
        $self->telegram = $args['telegram'] ?? null;
        $self->sort = $args['sort'] ?? 0;
        $self->active = $args['active'] ?? true;
        $self->type = $args['type'];

        foreach ($args['translations'] ?? [] as  $translation){
            $self->translations[] = DepartmentTranslationDTO::byArgs($translation);
        }

        $self->location = null;
        if(self::checkFieldExist($args, 'lat') && self::checkFieldExist($args, 'lon')){
            $self->location = new Point(trim($args['lat']), trim($args['lon']), 4326);
        }

        foreach ($args['schedule'] ?? [] as $schedule){
            $self->schedules[] = ScheduleDTO::byArgs($schedule);
        }

        return $self;
    }

    public function getEmail(): null|Email
    {
        return $this->email;
    }

    public function getPhone(): null|Phone
    {
        return $this->phone;
    }

    public function getViber(): null|string
    {
        return $this->viber;
    }

    public function getTelegram(): null|string
    {
        return $this->telegram;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTranslations(): array
    {
        return $this->translations;
    }

    public function getSchedule(): array
    {
        return $this->schedules;
    }

    public function getLocation(): null|Point
    {
        return $this->location;
    }
}

