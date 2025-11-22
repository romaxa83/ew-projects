<?php

namespace App\Services\Forms;

use App\Models\Forms\Draft;
use App\Models\Users\User;

class DraftService
{
    public function __construct()
    {}

    public function createOrUpdate(User $user, string $path, array $attributes)
    {
//        $attributes = $this->transform($path, $attributes);

//        logger_info('DraftService createOrUpdate', [
//            $attributes
//        ]);

        try {

            if($draft = $user->drafts()->where('path', $path)->first()) {
                $draft->body = $attributes;
            } else {
                $draft = new Draft();
                $draft->path = $path;
                $draft->body = $attributes;
                $draft->user_id = $user->id;

            }

            return $draft->save();

//            return $user->drafts()
//                ->where('path', $path)
//                ->first()
//                ->fill($attributes)
//                ->save();
        } catch (\Throwable $e) {
            logger_info($e);
        }

        try {
            return !!$user->drafts()->create($attributes);
        } catch (\Throwable $e) {
            logger_info($e);
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

    public function delete(Draft $model): bool
    {
        return $model->delete();
    }
}
