<?php

namespace App\Policies\Contacts;

use App\Models\Contacts\Contact;
use App\Models\Users\User;
use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;


    /**
     * @param User $user
     * @throws Exception
     */

    /**
     * Determine whether the user can create contacts.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if($user->can('contacts create')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the contacts list.
     *
     * @param User $user
     * @return mixed
     */
    public function viewList(User $user)
    {
        /*if($user->can('contacts read own')) {
            return true;
        } else*/ if($user->can('contacts read')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the contact.
     *
     * @param  User  $user
     * @param  Contact  $contact
     * @return mixed
     */
    public function view(User $user, Contact $contact)
    {
        /*if($user->can('contacts read own') && $user->id == $contact->user_id) {
            return true;
        } else*/ if($user->can('contacts read')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the contact.
     *
     * @param  User  $user
     * @param  Contact  $contact
     * @return mixed
     */
    public function update(User $user, Contact $contact)
    {
        /*if($user->can('contacts update own') && $user->id == $contact->user_id) {
            return true;
        } else*/ if($user->can('contacts update')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the contact.
     *
     * @param  User  $user
     * @param  Contact  $contact
     * @return mixed
     */
    public function delete(User $user, Contact $contact)
    {
        /*if($user->can('contacts delete own') && $user->id == $contact->user_id) {
            return true;
        } else*/ if($user->can('contacts delete')) {
            return true;
        }

        return false;
    }
}
