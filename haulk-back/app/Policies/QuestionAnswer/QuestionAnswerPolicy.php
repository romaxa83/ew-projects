<?php


namespace App\Policies\QuestionAnswer;

use App\Models\Users\User;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionAnswerPolicy
{
    use HandlesAuthorization;

    public function create(User $user)
    {
        if ($user->can('question-answer create')) {
            return true;
        }

        return false;
    }

    public function viewList(User $user)
    {
        if ($user->can('question-answer read')) {
            return true;
        }

        return false;
    }

    public function view(User $user)
    {
        if ($user->can('question-answer read')) {
            return true;
        }

        return false;
    }

    public function update(User $user)
    {
        if($user->can('question-answer update')) {
            return true;
        }

        return false;
    }

    public function delete(User $user)
    {
        if($user->can('question-answer delete')) {
            return true;
        }

        return false;
    }
}
