<?php

namespace App\Services\Technicians;

use App\Dto\Technicians\TechnicianDto;
use App\Events\Members\MemberProfileDeletedEvent;
use App\Events\Technicians\TechnicianRegisteredEvent;
use App\Exceptions\Technicians\TechnicianLicenseIsMissingException;
use App\Models\BaseAuthenticatable;
use App\Models\Locations\Country;
use App\Models\Technicians\Technician;
use App\Repositories\Locations\CountryRepository;
use App\Repositories\Technician\TechnicianRepository;
use App\Services\Auth\PhoneAuthService;
use App\Traits\Auth\PasswordGenerator;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TechnicianService
{
    use PasswordGenerator;

    public function __construct(
        protected PhoneAuthService $phoneAuthService,
        protected TechnicianVerificationService $technicianVerificationService,
        protected CountryRepository $countryRepository
    )
    {}

    public function toggleCertified(Technician $technician): bool
    {
        $technician->is_certified = !$technician->is_certified;

        return $technician->save();
    }

    public function toggleVerified(Technician $technician): bool
    {
        $technician->is_verified = !$technician->is_verified;

        return $technician->save();
    }

    public function toggleCommercialCertification(Technician $technician): bool
    {
        $technician->is_commercial_certification = !$technician->is_commercial_certification;

        return $technician->save();
    }

    /**
     * @param TechnicianDto $dto
     * @return Technician
     * @throws TechnicianLicenseIsMissingException
     */
    public function register(TechnicianDto $dto): Technician
    {
        $technician = $this->create($dto);

        event(new TechnicianRegisteredEvent($technician));

        return $technician;
    }

    /**
     * @param TechnicianDto $dto
     * @return Technician
     * @throws TechnicianLicenseIsMissingException
     */
    public function create(TechnicianDto $dto): Technician
    {
        $technician = new Technician();

        $this->fill($dto, $technician);

        $technician->setLicense($dto->getLicense());
        $technician->setPassword($dto->getPassword());
        $technician->save();

        return $technician;
    }

    protected function fill(TechnicianDto $dto, Technician $technician): void
    {
        if ($dto->hasSmsAccessToken()) {
            $this->phoneAuthService->confirmNewPhone($technician, $dto->getSmsAccessToken());
        } else {
            $technician->phone = $dto->getPhone();

            if ($technician->isDirty('phone')) {
                $technician->phone_verified_at = null;
            }
        }

        $technician->state_id = $dto->getStateId();
        $technician->country_id = $dto->getCountryId();
        $technician->first_name = $dto->getFirstName();
        $technician->last_name = $dto->getLastName();
        $technician->email = $dto->getEmail();

        $technician->setLanguage($dto->getLang());
    }

    /**
     * @param Technician $technician
     * @param TechnicianDto $dto
     * @return Technician
     * @throws TechnicianLicenseIsMissingException
     */
    public function update(Technician $technician, TechnicianDto $dto): Technician
    {
        $this->fill($dto, $technician);

        if ($dto->hasPassword()) {
            $technician->setPassword($dto->getPassword());
        }

        $technician->setLicense($dto->getLicense());

        $technician->save();

        return $technician;
    }

    public function changePassword(BaseAuthenticatable|Technician $technician, string $password): bool
    {
        return $technician->setPassword($password)->save();
    }

    public function confirmPhone(Technician $technician, string $smsAccessToken): bool
    {
        if ($this->phoneAuthService->confirmNewPhone($technician, $smsAccessToken)) {
            return $technician->save();
        }

        return false;
    }

    public function softDelete(Collection $models): bool
    {
        $models->each(
            fn(Technician $model): bool => $this->deleteProfile($model)
        );

        return true;
    }

    public function deleteProfile(Technician $technician, bool $force = false): bool
    {
        if ($force) {
            $this->clearRelations($technician);
        }

        event(new MemberProfileDeletedEvent($technician));

        return $force ? $technician->forceDelete() : $technician->delete();
    }

    public function clearRelations(Technician $technician): void
    {
        $technician->projects()->delete();
        $technician->alerts()->delete();
        $technician->conversations()->delete();
        $technician->rdpAccount()->delete();
        $technician->credentialRequests()->delete();
    }

    public function delete(Collection $models): bool
    {
        $models->each(
            function (Technician $model) {
                $this->deleteProfile($model, true);
            }
        );

        return true;
    }

    public function restore(Collection $models): bool
    {
        $models->each(fn(Technician $model) => $model->restore());

        return true;
    }

    public function changeEmailByEvent(Technician $technician): void
    {
        $technician->email_verified_at = null;

        $technician->save();

        try {
            $this->technicianVerificationService->verifyEmail($technician);
        } catch (Exception $e) {
            Log::error($e);
        }
    }

    public function setReModeration(Technician $technician): void
    {
        $technician->is_certified = false;
        $technician->is_verified = false;

        $technician->saveQuietly();
    }
}
