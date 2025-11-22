<?php

namespace App\Policies\Library;

use App\Models\Library\LibraryDocument;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LibraryDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool|void
     */
    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function create(User $user): bool
    {
        return $user->can('library create');
    }

    public function viewList(User $user): bool
    {
        return $user->can('library read');
    }

    public function view(User $user, LibraryDocument $document): bool
    {
        return $user->can('library read')
            && ($document->isPublic()
                || $document->isSharedToTheUser($user)
                || $document->isDownloadedByTheUser($user->id));
    }

    public function delete(User $user, LibraryDocument $document): bool
    {
        return $user->can('library delete')
            && $document->isDownloadedByTheUser($user->id);
    }
}
