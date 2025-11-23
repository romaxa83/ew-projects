<?php

namespace WezomCms\Users\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;
use WezomCms\Users\Models\SocialAccount;
use WezomCms\Users\Models\User;

class SocialAccountService
{
    /**
     * @param  string  $provider
     * @param  ProviderUser  $providerUser
     * @return mixed
     */
    public function createOrGetUser(string $provider, ProviderUser $providerUser)
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $providerUser->getId())
            ->first();

        if ($account) {
            return $account->user;
        } else {
            $account = new SocialAccount([
                'provider' => $provider,
                'provider_user_id' => $providerUser->getId(),
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
            ]);

            $user = $providerUser->getEmail() ? User::whereEmail($providerUser->getEmail())->first() : null;
            if (!$user) {
                $user = new User();
                $user->email = $providerUser->getEmail();
                $user->password = Hash::make(Str::random());
                $user->active = true;

                $raw = $providerUser->getRaw();

                if ($mapping = config("cms.users.users.socials.{$provider}.fields_mapping")) {
                    foreach ($mapping as $property => $providerField) {
                        $user->{$property} = array_get($raw, $providerField);
                    }
                } else {
                    $user->name = array_get($raw, 'first_name', $providerUser->getName());
                    $user->surname = array_get($raw, 'last_name');
                }

                $user->save();

                $user->markEmailAsVerified();

                event(new Registered($user));
            }

            $account->user()->associate($user);
            $account->save();

            return $user;
        }
    }

    /**
     * @param  string  $provider
     * @param  ProviderUser  $providerUser
     * @param $user
     * @return bool
     */
    public function connectWithAuthorizedUser(string $provider, ProviderUser $providerUser, $user)
    {
        $account = SocialAccount::whereProvider($provider)
            ->whereProviderUserId($providerUser->getId())
            ->first();

        if ($account) {
            if ($account->user_id == $user->id) {
                return true;
            } else {
                return false;
            }
        } else {
            $account = new SocialAccount([
                'provider' => $provider,
                'provider_user_id' => $providerUser->getId(),
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
            ]);

            $account->user()->associate($user);
            $account->save();

            return true;
        }
    }
}
