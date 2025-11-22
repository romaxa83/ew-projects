<?php

namespace App\Services\Forms;

use App\Models\Forms\Draft;
use App\Models\Users\User;
use Exception;
use Illuminate\Http\Response;
use Log;
use Throwable;

class DraftService
{
    /**
     * @param User $user
     * @param string $type
     * @return Draft
     * @throws Exception
     */
    public function show(User $user, string $type): ?Draft
    {
        return $this->findByUserAndPath($user, $type);
    }

    /**
     * @param User $user
     * @param string $path
     * @return Draft|null
     * @throws Exception
     */
    protected function findByUserAndPath(User $user, string $path): ?Draft
    {
        if (!($draft = $user->findDraftByPath($path))) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return $draft;
    }

    public function createOrUpdate(User $user, string $path, array $attributes): bool
    {
        $attributes = $this->transform($path, $attributes);

        try {
            return $this->findByUserAndPath($user, $path)
                ->fill($attributes)
                ->save();
        } catch (Throwable $exception) {
        }

        try {
            return !!$user->drafts()->create($attributes);
        } catch (Throwable $exception) {
            Log::error($exception);
        }

        return false;
    }

    protected function transform(string $path, array $attributes): array
    {
        return [
            'path' => $path,
            'body' => $attributes,
        ];
    }

    /**
     * @param User $user
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    public function delete(User $user, string $path)
    {
        return $this->findByUserAndPath($user, $path)->delete();
    }
}
