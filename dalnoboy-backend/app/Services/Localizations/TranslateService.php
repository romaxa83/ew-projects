<?php


namespace App\Services\Localizations;


use App\Dto\Localizations\TranslateDto;
use App\Exceptions\Localizations\TranslateExistsException;
use App\Models\Admins\Admin;
use App\Models\Localization\Translate;
use App\Models\Users\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TranslateService
{
    public function create(TranslateDto $dto): Translate
    {
        return $this->editTranslate($dto, new Translate());
    }

    public function editTranslate(TranslateDto $dto, Translate $translate): Translate
    {
        $exists = Translate::query()
            ->where('place', $dto->getPlace())
            ->where('key', $dto->getKey())
            ->where('lang', $dto->getLang())
            ->where('id', '<>', $translate->id)
            ->exists();

        if ($exists) {
            throw new TranslateExistsException();
        }

        $translate->place = $dto->getPlace();
        $translate->key = $dto->getKey();
        $translate->lang = $dto->getLang();
        $translate->text = $dto->getText();

        if ($translate->isDirty()) {
            $translate->save();
            Translate::flushQueryCache(['localization']);
        }

        return $translate->refresh();
    }

    public function update(TranslateDto $dto, Translate $translate): Translate
    {
        return $this->editTranslate($dto, $translate);
    }

    public function delete(Translate $translate): bool
    {
        Translate::flushQueryCache(['localization']);
        return $translate->delete();
    }

    public function setGuardLanguage(Admin|User $user, string $language): bool
    {
        $user->lang = $language;
        $user->save();
        return true;
    }

    public function show(array $args, array $select): LengthAwarePaginator
    {
        return Translate::filter($args)
            ->select($select)
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }

    public function getList(array $args): Collection
    {
        return Translate::filter($args)
            ->cacheFor(config('queries.localization.translates.cache'))
            ->cacheTags(['localization'])
            ->get();
    }
}
